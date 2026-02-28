import { useState } from 'react';

const US_STATES = [
  ['AL','Alabama'], ['AK','Alaska'], ['AZ','Arizona'], // ... all 50
  ['TX','Texas'], ['UT','Utah'], ['WA','Washington'],
];

export default function AddressForm({ initial = {}, onSubmit, onCancel, submitting }) {
  const [form, setForm] = useState({
    first_name:    initial.first_name    ?? '',
    last_name:     initial.last_name     ?? '',
    phone:         initial.phone         ?? '',
    address_line1: initial.address_line1 ?? '',
    address_line2: initial.address_line2 ?? '',
    city:          initial.city          ?? '',
    state:         initial.state         ?? '',
    zip_code:      initial.zip_code      ?? '',
    is_default:    initial.is_default    ?? true,
    label:         initial.label         ?? '',
  });
  const [errors, setErrors] = useState({});

  const set = (field) => (e) => {
    setForm(f => ({ ...f, [field]: e.target.value }));
    setErrors(err => ({ ...err, [field]: null }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // Basic client-side validation before hitting the API
    const errs = {};
    if (!form.first_name.trim()) errs.first_name    = 'Required';
    if (!form.last_name.trim())  errs.last_name     = 'Required';
    if (!form.address_line1.trim()) errs.address_line1 = 'Required';
    if (!form.city.trim())       errs.city          = 'Required';
    if (!form.state)             errs.state         = 'Required';
    if (!form.zip_code.trim())   errs.zip_code      = 'Required';
    if (Object.keys(errs).length) { setErrors(errs); return; }
    onSubmit(form, setErrors); // pass setErrors so parent can inject API errors
  };

  const field = (label, key, props = {}) => (
    <div>
      <label className="block text-xs font-bold text-gray-600 mb-1">{label}</label>
      <input
        value={form[key]}
        onChange={set(key)}
        className={`w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500
          ${errors[key] ? 'border-red-400' : 'border-gray-200'}`}
        {...props}
      />
      {errors[key] && <p className="text-xs text-red-500 mt-1">{errors[key]}</p>}
    </div>
  );

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid grid-cols-2 gap-3">
        {field('First name', 'first_name', { placeholder: 'John' })}
        {field('Last name',  'last_name',  { placeholder: 'Doe'  })}
      </div>
      {field('Phone (optional)', 'phone', { placeholder: '+1 555 000 0000', type: 'tel' })}
      {field('Address line 1', 'address_line1', { placeholder: '123 Main St' })}
      {field('Address line 2 (optional)', 'address_line2', { placeholder: 'Apt, suite, etc.' })}
      <div className="grid grid-cols-3 gap-3">
        {field('City', 'city')}
        <div>
          <label className="block text-xs font-bold text-gray-600 mb-1">State</label>
          <select
            value={form.state}
            onChange={set('state')}
            className={`w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500
              ${errors.state ? 'border-red-400' : 'border-gray-200'}`}
          >
            <option value="">—</option>
            {US_STATES.map(([code, name]) => (
              <option key={code} value={code}>{code} – {name}</option>
            ))}
          </select>
          {errors.state && <p className="text-xs text-red-500 mt-1">{errors.state}</p>}
        </div>
        {field('ZIP', 'zip_code', { placeholder: '90210', maxLength: 10 })}
      </div>
      <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
        <input
          type="checkbox"
          checked={form.is_default}
          onChange={e => setForm(f => ({ ...f, is_default: e.target.checked }))}
          className="rounded border-gray-300 text-green-600"
        />
        Save as default address
      </label>

      <div className="flex gap-3 pt-2">
        {onCancel && (
          <button type="button" onClick={onCancel}
            className="flex-1 border border-gray-200 text-gray-700 font-bold py-3 rounded-xl text-sm hover:bg-gray-50 transition-colors">
            Cancel
          </button>
        )}
        <button type="submit" disabled={submitting}
          className="flex-1 bg-green-700 hover:bg-green-800 disabled:opacity-60 text-white font-bold py-3 rounded-xl text-sm transition-colors">
          {submitting ? 'Saving…' : 'Save address'}
        </button>
      </div>
    </form>
  );
}