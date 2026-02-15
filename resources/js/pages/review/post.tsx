import React, { useState, useRef } from 'react';
import { Star, UploadCloud, Check, X, ChevronLeft, AlertCircle } from 'lucide-react';
import { Link, useForm, router } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head } from '@inertiajs/react';

declare function route(name: string, params?: any): string;

interface Product {
    id: number;
    name: string;
    slug: string;
    subtitle: string;
    image: string;
}

interface Props {
    product: Product;
}

export default function CreateReviewPage({ product }: Props) {
    const [hoverRating, setHoverRating] = useState(0);
    const [imagePreviews, setImagePreviews] = useState<string[]>([]);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        rating:    0,
        title:     '',
        content:   '',
        recommend: null as boolean | null,
        name:      '',
        email:     '',
        location:  '',
        images:    [] as File[],
    });

    const ratingLabels: Record<number, string> = {
        1: 'Poor',
        2: 'Fair',
        3: 'Average',
        4: 'Great',
        5: 'Excellent!',
    };

    const handleImageSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files ?? []);
        const remaining = 4 - data.images.length;
        const selected = files.slice(0, remaining);

        // Generate previews
        selected.forEach((file) => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                setImagePreviews((prev) => [...prev, ev.target?.result as string]);
            };
            reader.readAsDataURL(file);
        });

        setData('images', [...data.images, ...selected]);
    };

    const removeImage = (index: number) => {
        setImagePreviews((prev) => prev.filter((_, i) => i !== index));
        setData('images', data.images.filter((_, i) => i !== index));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Use router.post with FormData for file uploads
        const formData = new FormData();
        formData.append('rating',    String(data.rating));
        formData.append('title',     data.title);
        formData.append('content',   data.content);
        formData.append('name',      data.name);
        formData.append('location',  data.location);
        if (data.recommend !== null) {
            formData.append('recommend', data.recommend ? '1' : '0');
        }
        data.images.forEach((file) => {
            formData.append('images[]', file);
        });

        router.post(route('product.review.store', product.slug), formData, {
            forceFormData: true,
        });
    };

    return (
        <AppHeaderLayout>
            <Head title={`Review ${product.name}`} />
            <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                {/* Breadcrumb */}
                <div className="mb-8">
                    <Link
                        href={route('products.show', product.slug)}
                        className="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#2E7D32] transition-colors"
                    >
                        <ChevronLeft size={16} />
                        Back to {product.name}
                    </Link>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20">

                    {/* Left: Product Summary & Tips */}
                    <div className="lg:col-span-4 space-y-8">
                        <div className="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm text-center">
                            <div className="w-48 h-48 mx-auto bg-gray-50 rounded-2xl mb-6 overflow-hidden flex items-center justify-center">
                                <img
                                    src={product.image}
                                    alt={product.name}
                                    className="w-full h-full object-contain mix-blend-multiply p-4"
                                />
                            </div>
                            <h2 className="text-xl font-black font-serif text-gray-900 mb-2">{product.name}</h2>
                            {product.subtitle && (
                                <p className="text-sm text-gray-500 font-medium">{product.subtitle}</p>
                            )}
                        </div>

                        <div className="bg-[#E8F5E9] p-6 rounded-3xl border border-green-100">
                            <h3 className="font-bold text-[#1B5E20] mb-4 flex items-center gap-2">
                                <Star size={18} className="fill-[#1B5E20]" />
                                Writing a great review
                            </h3>
                            <ul className="space-y-3 text-sm text-[#2E7D32]">
                                <li className="flex gap-3">
                                    <span className="font-bold">•</span>
                                    <span>Focus on specific results (e.g., "Weeds died in 3 days").</span>
                                </li>
                                <li className="flex gap-3">
                                    <span className="font-bold">•</span>
                                    <span>Mention your location or grass type if relevant.</span>
                                </li>
                                <li className="flex gap-3">
                                    <span className="font-bold">•</span>
                                    <span><strong>Pro Tip:</strong> Photos make your review 3x more helpful!</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {/* Right: Review Form */}
                    <div className="lg:col-span-8">
                        <h1 className="text-3xl md:text-4xl font-black font-serif text-gray-900 mb-2">
                            Write a Review
                        </h1>
                        <p className="text-gray-500 text-lg mb-10">
                            Share your experience with the TurfTec community.
                        </p>

                        <form onSubmit={handleSubmit} className="space-y-8">

                            {/* Rating */}
                            <div className="space-y-4">
                                <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                    Overall Rating <span className="text-red-500">*</span>
                                </label>
                                <div className="flex items-center gap-2">
                                    {[1, 2, 3, 4, 5].map((star) => (
                                        <button
                                            key={star}
                                            type="button"
                                            onMouseEnter={() => setHoverRating(star)}
                                            onMouseLeave={() => setHoverRating(0)}
                                            onClick={() => setData('rating', star)}
                                            className="p-1 focus:outline-none transition-transform hover:scale-110"
                                        >
                                            <Star
                                                size={40}
                                                className={`transition-colors duration-200 ${
                                                    star <= (hoverRating || data.rating)
                                                        ? 'fill-[#FDB94E] text-[#FDB94E]'
                                                        : 'fill-transparent text-gray-200'
                                                }`}
                                                strokeWidth={1.5}
                                            />
                                        </button>
                                    ))}
                                    <span className="ml-4 text-lg font-bold text-gray-400">
                                        {ratingLabels[hoverRating || data.rating] ?? ''}
                                    </span>
                                </div>
                                {errors.rating && (
                                    <p className="text-red-500 text-sm flex items-center gap-1">
                                        <AlertCircle size={14} /> {errors.rating}
                                    </p>
                                )}
                            </div>

                            {/* Title + Location */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                        Review Title
                                    </label>
                                    <input
                                        type="text"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        placeholder="Sum up your experience"
                                        className="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent transition-all placeholder:text-gray-400 font-medium"
                                    />
                                    {errors.title && <p className="text-red-500 text-sm">{errors.title}</p>}
                                </div>
                                <div className="space-y-2">
                                    <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                        Location (Optional)
                                    </label>
                                    <input
                                        type="text"
                                        value={data.location}
                                        onChange={(e) => setData('location', e.target.value)}
                                        placeholder="e.g. Austin, TX"
                                        className="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent transition-all placeholder:text-gray-400 font-medium"
                                    />
                                </div>
                            </div>

                            {/* Review Body */}
                            <div className="space-y-2">
                                <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                    Your Review
                                </label>
                                <textarea
                                    rows={5}
                                    value={data.content}
                                    onChange={(e) => setData('content', e.target.value)}
                                    placeholder="What did you like or dislike? How was the application process?"
                                    className="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent transition-all placeholder:text-gray-400 font-medium resize-none"
                                />
                                {errors.content && <p className="text-red-500 text-sm">{errors.content}</p>}
                            </div>

                            {/* Photo Upload */}
                            <div className="space-y-4">
                                <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                    Add Photos (up to 4)
                                </label>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    {imagePreviews.map((src, idx) => (
                                        <div
                                            key={idx}
                                            className="relative aspect-square rounded-xl overflow-hidden group border border-gray-100"
                                        >
                                            <img src={src} alt="" className="w-full h-full object-cover" />
                                            <button
                                                type="button"
                                                onClick={() => removeImage(idx)}
                                                className="absolute top-2 right-2 bg-white/90 p-1 rounded-full text-red-500 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm"
                                            >
                                                <X size={16} />
                                            </button>
                                        </div>
                                    ))}

                                    {imagePreviews.length < 4 && (
                                        <>
                                            <button
                                                type="button"
                                                onClick={() => fileInputRef.current?.click()}
                                                className="aspect-square rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 hover:border-[#2E7D32] hover:text-[#2E7D32] hover:bg-green-50/30 transition-all gap-2 group"
                                            >
                                                <div className="p-3 bg-gray-50 rounded-full group-hover:bg-white transition-colors">
                                                    <UploadCloud size={24} />
                                                </div>
                                                <span className="text-xs font-bold">Upload</span>
                                            </button>
                                            <input
                                                ref={fileInputRef}
                                                type="file"
                                                accept="image/*"
                                                multiple
                                                className="hidden"
                                                onChange={handleImageSelect}
                                            />
                                        </>
                                    )}
                                </div>
                            </div>

                            {/* Recommendation */}
                            <div className="space-y-4 pt-4 border-t border-gray-100">
                                <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                    Would you recommend this product?
                                </label>
                                <div className="flex gap-4">
                                    <button
                                        type="button"
                                        onClick={() => setData('recommend', true)}
                                        className={`flex-1 py-3 rounded-xl border-2 font-bold text-sm flex items-center justify-center gap-2 transition-all ${
                                            data.recommend === true
                                                ? 'border-[#2E7D32] bg-green-50 text-[#1B5E20]'
                                                : 'border-gray-200 text-gray-500 hover:border-gray-300'
                                        }`}
                                    >
                                        <Check size={18} /> Yes
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setData('recommend', false)}
                                        className={`flex-1 py-3 rounded-xl border-2 font-bold text-sm flex items-center justify-center gap-2 transition-all ${
                                            data.recommend === false
                                                ? 'border-gray-900 bg-gray-50 text-gray-900'
                                                : 'border-gray-200 text-gray-500 hover:border-gray-300'
                                        }`}
                                    >
                                        <X size={18} /> No
                                    </button>
                                </div>
                            </div>

                            {/* Name + Email */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                <div className="space-y-2">
                                    <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                        Name <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Your name"
                                        className="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent transition-all placeholder:text-gray-400 font-medium"
                                    />
                                    {errors.name && <p className="text-red-500 text-sm">{errors.name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <label className="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                                        Email (Private)
                                    </label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="your@email.com"
                                        className="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent transition-all placeholder:text-gray-400 font-medium"
                                    />
                                </div>
                            </div>

                            {/* Submit */}
                            <div className="pt-6">
                                <button
                                    type="submit"
                                    disabled={processing || data.rating === 0}
                                    className="w-full md:w-auto bg-[#2E7D32] hover:bg-[#1B5E20] disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-extrabold text-lg py-4 px-12 rounded-xl shadow-lg shadow-green-900/20 transition-all active:scale-[0.98]"
                                >
                                    {processing ? 'Submitting...' : 'Submit Review'}
                                </button>
                                <p className="text-xs text-gray-400 mt-4">
                                    By submitting, you agree to our{' '}
                                    <a href="#" className="underline hover:text-[#2E7D32]">Terms & Conditions</a>.
                                    Reviews are subject to moderation before publishing.
                                </p>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </AppHeaderLayout>
    );
}