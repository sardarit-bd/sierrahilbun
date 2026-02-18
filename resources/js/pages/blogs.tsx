import React, { useState } from 'react';
import { Calendar, Clock, User, ArrowRight, Search, ChevronRight, Tag, Mail, Leaf } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, Link } from '@inertiajs/react';

// --- Types & Interfaces ---
interface BlogPost {
  id: number;
  title: string;
  excerpt: string;
  image: string;
  category: string;
  category_slug?: string;
  author: string;
  date: string;
  readTime?: string;
  featured: boolean;
}

// --- Sub-Components ---

const FeaturedPost = ({ post }: { post: BlogPost }) => (
  <div className="relative group rounded-[2rem] overflow-hidden shadow-2xl mb-16 isolate">
    <div className="absolute inset-0 bg-gray-900/40 group-hover:bg-gray-900/30 transition-colors duration-500 z-10"></div>
    <img 
      src={post.image} 
      alt={post.title} 
      className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 z-0"
    />
  </div>
);

const PostCard = ({ post }: { post: BlogPost }) => (
  <div className="group flex flex-col h-full bg-white rounded-3xl overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-[0_20px_40px_-15px_rgba(46,125,50,0.15)] hover:-translate-y-1">
    <div className="relative h-64 overflow-hidden">
      <img 
        src={post.image} 
        alt={post.title} 
        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
      />
      <div className="absolute top-4 left-4 bg-white/90 backdrop-blur text-[#2E7D32] text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
        {post.category}
      </div>
    </div>
    
    <div className="flex-1 p-8 flex flex-col">
      <div className="flex items-center gap-3 text-xs text-gray-500 font-medium mb-4 uppercase tracking-wide">
        <span>{post.date}</span>
        {post.readTime && (
          <>
            <span className="w-1 h-1 bg-gray-300 rounded-full"></span>
            <span>{post.readTime}</span>
          </>
        )}
      </div>
      
      <h3 className="text-xl font-bold text-gray-900 mb-3 font-serif leading-tight group-hover:text-[#2E7D32] transition-colors">
        {post.title}
      </h3>
      
      <p className="text-gray-500 text-sm leading-relaxed mb-6 flex-1 line-clamp-3">
        {post.excerpt}
      </p>
      
      <div className="flex items-center justify-between border-t border-gray-100 pt-6 mt-auto">
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
            <User size={12} />
          </div>
          <span className="text-xs font-bold text-gray-700">{post.author}</span>
        </div>
        <Link href={`/blogs/${post.slug}`} className="text-[#2E7D32] font-bold text-sm flex items-center gap-1 group/btn">
          Read Article 
          <ChevronRight size={16} className="transition-transform group-hover/btn:translate-x-1" />
        </Link>
      </div>
    </div>
  </div>
);

// --- Main Page Component ---

interface BlogPageProps {
  posts: BlogPost[];
  categories: { name: string; slug: string }[];
  filters?: { category?: string; search?: string };
}

export default function BlogPage({ posts, categories, filters }: BlogPageProps) {
  const postsArray = Array.isArray(posts) ? posts : posts.data;

  const [activeCategory, setActiveCategory] = useState(filters?.category ?? 'All');
  const [searchQuery, setSearchQuery] = useState(filters?.search ?? '');

  const featuredPost = postsArray.find(p => p.featured);
  const regularPosts = postsArray.filter(p => !p.featured);

  const filteredPosts = regularPosts.filter(post => {
    const matchesCategory = activeCategory === 'All' || post.category === activeCategory;
    const matchesSearch = post.title.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCategory && matchesSearch;
  });

  return (
    <AppHeaderLayout>
      <Head title="All Blogs" />
      <div className="w-full max-w-7xl mx-auto px-6 py-12">
        
        {/* Header Section */}
        <div className="text-center max-w-3xl mx-auto mb-16">
          <div className="inline-flex items-center gap-2 text-[#2E7D32] font-bold uppercase tracking-widest text-xs bg-green-50 px-4 py-2 rounded-full mb-6">
            <Leaf size={14} />
            <span>TurfTalk Blog</span>
          </div>
          <h1 className="text-4xl md:text-6xl font-black text-gray-900 mb-6 font-serif leading-tight">
            Expert Advice for a <br />
            <span className="inline-block text-transparent bg-clip-text bg-gradient-to-r from-[#2E7D32] to-[#4CAF50]">Better Lawn</span>
          </h1>
          <p className="text-lg text-gray-500 leading-relaxed">
            Discover tips, science, and guides from our agronomy team. We make professional lawn care accessible to every homeowner.
          </p>
        </div>

        {/* Featured Post */}
        {featuredPost && <FeaturedPost post={featuredPost} />}

        {/* Filters & Search */}
        <div className="sticky top-4 z-30 mb-12">
           <div className="bg-white/80 backdrop-blur-xl border border-white/40 shadow-xl shadow-gray-200/50 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
            
            {/* Category Pills */}
            <div className="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 no-scrollbar">
              {categories.map((cat) => (
                <button
                  key={cat.name}
                  onClick={() => setActiveCategory(cat.name)}
                  className={`px-5 py-2.5 rounded-xl text-sm font-bold transition-all whitespace-nowrap ${
                    activeCategory === cat.name
                      ? 'bg-[#1A1A1A] text-white shadow-lg'
                      : 'bg-transparent text-gray-500 hover:bg-gray-50 hover:text-gray-900'
                  }`}
                >
                  {cat.name}
                </button>
              ))}
            </div>

            {/* Search Bar */}
            <div className="relative w-full md:w-72">
              <input
                type="text"
                placeholder="Search articles..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-10 pr-4 py-2.5 bg-gray-50 text-gray-900 border border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent rounded-xl text-sm font-medium outline-none transition-all"
              />
              <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
            </div>
          </div>
        </div>

        {/* Blog Grid */}
        {filteredPosts.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
            {filteredPosts.map(post => (
              <PostCard key={post.id} post={post} />
            ))}
          </div>
        ) : (
          <div className="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200 mb-20">
            <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
              <Search size={32} />
            </div>
            <h3 className="text-xl font-bold text-gray-900 mb-2">No articles found</h3>
            <p className="text-gray-500">Try changing your search term or category.</p>
          </div>
        )}

      </div>
    </AppHeaderLayout>
  );
}
