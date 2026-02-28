import { useState, useEffect } from 'react';
import { X, MapPin } from 'lucide-react';
import AddressCard from './AddressCard';
import AddressForm from './AddressForm';

const VIEW = { LOADING: 'loading', CONFIRM: 'confirm', FORM: 'form', ERROR: 'error' };

export default function ShippingAddressModal({ isOpen, onClose, onConfirmed }) {
  const [view,       setView]       = useState(VIEW.LOADING);
  const [addresses,  setAddresses]  = useState([]);
  const [editing,    setEditing]    = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [fetchError, setFetchError] = useState('');

  // Lock scroll
  useEffect(() => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [isOpen]);

  // Fetch addresses when modal opens
  useEffect(() => {
    if (!isOpen) return;
    setView(VIEW.LOADING);
    setFetchError('');

    fetch('/api/shipping-addresses', {        
      headers: {
        'Accept':       'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
    })
      .then(async (r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then((data) => {
        // Handle both plain array and Laravel paginator { data: [...] }
        const list: any[] = Array.isArray(data) ? data : (data?.data ?? []);
        setAddresses(list);
        const def = list.find((a) => a.is_default) ?? list[0];
        setEditing(null);
        setView(def ? VIEW.CONFIRM : VIEW.FORM);
      })
      .catch((err) => {
        console.error('Failed to load addresses:', err);
        setFetchError('Failed to load addresses. Please try again.');
        setView(VIEW.ERROR);
      });
  }, [isOpen]);

  if (!isOpen) return null;

  const defaultAddress = addresses.find((a) => a.is_default) ?? addresses[0] ?? null;

  // ── Save (create or update) ──────────────────────────────
  const handleSave = async (formData, setErrors) => {
    setSubmitting(true);
    const isUpdate = editing?.id != null;
    const url      = isUpdate
      ? `/api/shipping-addresses/${editing.id}`
      : '/api/shipping-addresses';

    try {
      const res = await fetch(url, {
        method:      isUpdate ? 'PUT' : 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept':       'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify(formData),
      });

      const data = await res.json();

      if (!res.ok) {
        if (data.errors) {
          setErrors(
            Object.fromEntries(
              Object.entries(data.errors).map(([k, v]: [string, any]) => [k, v[0]])
            )
          );
        } else {
          setErrors({ address_line1: data.message || 'Something went wrong.' });
        }
        return;
      }

      // Update local list
      setAddresses((prev) => {
        const withoutOld = prev.filter((a) => a.id !== data.id);
        const updated    = formData.is_default
          ? withoutOld.map((a) => ({ ...a, is_default: false }))
          : withoutOld;
        return [...updated, data];
      });

      setView(VIEW.CONFIRM);
    } catch {
      setErrors({ address_line1: 'Network error, please try again.' });
    } finally {
      setSubmitting(false);
    }
  };

  // ── Confirm ──────────────────────────────────────────────
  const handleConfirm = () => {
    const confirmed = addresses.find((a) => a.is_default) ?? addresses[0];
    onConfirmed(confirmed);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />

      <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center">
              <MapPin size={18} className="text-green-700" />
            </div>
            <div>
              <h3 className="font-bold text-gray-900 text-base leading-none">Shipping Address</h3>
              <p className="text-xs text-gray-400 mt-0.5">
                {view === VIEW.CONFIRM ? 'Confirm your delivery address' : 'Enter your delivery address'}
              </p>
            </div>
          </div>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors">
            <X size={20} />
          </button>
        </div>

        {/* Body */}
        <div className="px-6 py-5 overflow-y-auto">

          {/* Loading */}
          {view === VIEW.LOADING && (
            <div className="flex items-center justify-center py-10">
              <div className="w-8 h-8 border-[3px] border-green-600 border-t-transparent rounded-full animate-spin" />
            </div>
          )}

          {/* Error */}
          {view === VIEW.ERROR && (
            <div className="text-center py-8">
              <p className="text-sm text-red-500 mb-4">{fetchError}</p>
              <button
                onClick={() => setView(VIEW.LOADING)}
                className="text-sm font-bold text-green-700 hover:underline"
              >
                Try again
              </button>
            </div>
          )}

          {/* Confirm existing address */}
          {view === VIEW.CONFIRM && defaultAddress && (
            <div className="space-y-4">
              <p className="text-sm text-gray-500">Is this address still correct for your delivery?</p>
              <AddressCard
                address={defaultAddress}
                onConfirm={handleConfirm}
                onEdit={() => { setEditing(defaultAddress); setView(VIEW.FORM); }}
              />

              {/* Other saved addresses */}
              {addresses.length > 1 && (
                <div>
                  <p className="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                    Other saved addresses
                  </p>
                  <div className="space-y-2">
                    {addresses
                      .filter((a) => a.id !== defaultAddress.id)
                      .map((a) => (
                        <button
                          key={a.id}
                          onClick={() => { setEditing(a); setView(VIEW.FORM); }}
                          className="w-full text-left border border-gray-100 rounded-xl px-4 py-3 hover:border-green-300 hover:bg-green-50 transition-colors text-sm"
                        >
                          <p className="font-semibold text-gray-800">{a.first_name} {a.last_name}</p>
                          <p className="text-gray-500 text-xs">{a.address_line1}, {a.city}, {a.state}</p>
                        </button>
                      ))
                    }
                  </div>
                </div>
              )}

              <button
                onClick={() => { setEditing(null); setView(VIEW.FORM); }}
                className="w-full text-center text-sm font-bold text-green-700 hover:text-green-800 py-2 transition-colors"
              >
                + Add a new address
              </button>
            </div>
          )}

          {/* Form — new or edit */}
          {view === VIEW.FORM && (
            <AddressForm
              initial={editing ?? {}}
              submitting={submitting}
              onSubmit={handleSave}
              onCancel={addresses.length > 0 ? () => setView(VIEW.CONFIRM) : null}
            />
          )}
        </div>
      </div>
    </div>
  );
}