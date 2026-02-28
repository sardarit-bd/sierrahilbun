const ToggleSwitch = ({ enabled, onToggle }) => (
  <button
    onClick={onToggle}
    className={`relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none ${enabled ? 'bg-green-700' : 'bg-gray-300'}`}
  >
    <span className={`inline-block h-6 w-6 transform rounded-full bg-white transition duration-300 ease-in-out shadow-sm ${enabled ? 'translate-x-7' : 'translate-x-1'}`} />
  </button>
);

export default ToggleSwitch;