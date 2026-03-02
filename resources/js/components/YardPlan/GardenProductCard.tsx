import ProductCard from './ProductCard';

const GardenProductCard = ({ feature, item, index }) => {
  const quartsLabel = item
    ? `${item.quarts} quart${item.quarts !== 1 ? 's' : ''} · $${item.price_per_quart}/quart · $${item.total.toFixed(2)} total`
    : null;
  const mergedFeature = { ...feature, subtitle: quartsLabel ?? feature.subtitle };
  return <ProductCard feature={mergedFeature} index={index} />;
};

export default GardenProductCard;