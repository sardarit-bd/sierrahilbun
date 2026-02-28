import { MapPin, Pencil } from 'lucide-react';

export default function AddressCard({ address, onConfirm, onEdit }) {
  return (
    <div className="border-2 border-green-600 bg-green-50 rounded-xl p-4">
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-start gap-3">
          <div className="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
            <MapPin size={18} className="text-green-700" />
          </div>
          <div>
            {address.label && (
              <p className="text-[10px] font-bold uppercase tracking-widest text-green-700 mb-0.5">
                {address.label}
              </p>
            )}
            <p className="font-bold text-gray-900 text-sm">
              {address.first_name} {address.last_name}
            </p>
            <p className="text-sm text-gray-600 mt-0.5">{address.address_line1}</p>
            {address.address_line2 && (
              <p className="text-sm text-gray-600">{address.address_line2}</p>
            )}
            <p className="text-sm text-gray-600">
              {address.city}, {address.state} {address.zip_code}
            </p>
            {address.phone && (
              <p className="text-xs text-gray-400 mt-1">{address.phone}</p>
            )}
          </div>
        </div>
        <button onClick={onEdit}
          className="text-gray-400 hover:text-green-700 transition-colors flex-shrink-0">
          <Pencil size={15} />
        </button>
      </div>

      <div className="flex gap-3 mt-4">
        <button onClick={onEdit}
          className="flex-1 border border-gray-200 text-gray-700 font-bold py-2.5 rounded-xl text-sm hover:bg-white transition-colors">
          Change
        </button>
        <button onClick={onConfirm}
          className="flex-1 bg-green-700 hover:bg-green-800 text-white font-bold py-2.5 rounded-xl text-sm transition-colors">
          Confirm & Continue
        </button>
      </div>
    </div>
  );
}