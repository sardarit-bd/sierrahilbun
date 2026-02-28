export const resolveUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  if (!path.includes('/')) return `/storage/plan-features/${path}`;
  if (!path.startsWith('/storage') && !path.startsWith('storage')) return `/storage/${path}`;
  return path.startsWith('/') ? path : `/${path}`;
};

export const FALLBACK_IMAGE =
  'https://images.unsplash.com/photo-1605117882932-f9e32b03fea9?q=80&w=400&auto=format&fit=crop';

export const PRICING_TYPE_STYLES = {
  included: { bg: 'bg-green-100', text: 'text-green-800', label: 'Included' },
  addon:    { bg: 'bg-amber-100', text: 'text-amber-800', label: 'Add-on'   },
};

export const GARDEN_TYPES = [
  { value: 'flowers',      label: 'Flowers'       },
  { value: 'vegetables',   label: 'Vegetables'     },
  { value: 'trees_shrubs', label: 'Trees & Shrubs' },
];

export const GARDEN_SIZES = [
  { value: 'xs', label: 'XS',  description: 'Less than 500 sq ft' },
  { value: 'sm', label: 'S-M', description: '500–1,000 sq ft'     },
  { value: 'l',  label: 'L',   description: '1,000+ sq ft'        },
];

const CARD_ASSETS = [
  { color: 'bg-green-100 text-green-800',   img: 'https://images.unsplash.com/photo-1605117882932-f9e32b03fea9?q=80&w=1019&auto=format&fit=crop', defaultTag: 'Essential' },
  { color: 'bg-green-100 text-green-800',   img: 'https://images.unsplash.com/photo-1621778029697-e648b727ddc7?q=80&w=828&auto=format&fit=crop',  defaultTag: 'Control'   },
  { color: 'bg-green-100 text-green-800',   img: 'https://images.unsplash.com/photo-1590682680695-43b964a3ae17?auto=format&fit=crop&q=80&w=200',   defaultTag: 'Growth'    },
  { color: 'bg-[#2A9D8F] text-white',       img: 'https://plus.unsplash.com/premium_photo-1729087867520-6b9a869ed39a?q=80&w=735&auto=format&fit=crop', defaultTag: 'Analysis' },
  { color: 'bg-orange-100 text-orange-800', img: 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&q=80&w=200',     defaultTag: 'Bonus'     },
];

export const mapFeatureToAsset = (feature, index) => {
  const asset    = CARD_ASSETS[index % CARD_ASSETS.length];
  const iconUrl  = feature.icon_url ? resolveUrl(feature.icon_url) : asset.img;
  const images   = Array.isArray(feature.image_url)
    ? feature.image_url
    : typeof feature.image_url === 'string'
      ? JSON.parse(feature.image_url)
      : [];
  const expandedImageUrl = images.length ? resolveUrl(images[0]) : null;
  return { ...feature, tag: feature.tag || asset.defaultTag, tagColor: asset.color, displayIcon: iconUrl, displayImage: expandedImageUrl };
};