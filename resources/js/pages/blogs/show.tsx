import { useState }  from 'react';
import { Calendar, Clock, User, Facebook, Twitter, Linkedin, Link as LinkIcon, ChevronLeft, ArrowRight, Mail } from 'lucide-react';
import { Link, Head, usePage } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';

interface BlogPost {
  id: number;
  title: string;
  slug: string;
  content: string;
  image: string;
  author: string;
  category: string;
  category_slug: string;
  date: string;
  tags: string[];
}

interface PageProps {
  post: BlogPost;
  [key: string]: unknown;
}

export default function BlogPostPage() {
  const { post } = usePage<PageProps>().props;

  const [copied, setCopied] = useState(false);

  const handleCopyLink = async () => {
    try {
      await navigator.clipboard.writeText(window.location.href);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch {
      // Fallback for older browsers
      const input = document.createElement('input');
      input.value = window.location.href;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  return (
    <AppHeaderLayout>
      <Head title={post.title} />

      <article className="pt-20 pb-24">
        
        {/* Breadcrumb / Back */}
        <div className="max-w-4xl mx-auto px-6 mb-8">
          <Link href="/blogs" className="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#2E7D32] transition-colors">
            <ChevronLeft size={16} />
            Back to Blog
          </Link>
        </div>

        {/* Article Header */}
        <header className="max-w-4xl mx-auto px-6 text-center mb-12">
          <div className="inline-block px-3 py-1 mb-6 text-xs font-bold tracking-widest text-white uppercase bg-[#2E7D32] rounded-full">
            {post.category}
          </div>
          <h1 className="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 mb-6 font-serif leading-tight">
            {post.title}
          </h1>

          <div className="flex flex-col sm:flex-row items-center justify-center gap-6 text-sm text-gray-500 font-semibold">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-[#2E7D32] font-bold text-sm border-2 border-white shadow-sm">
                {post.author?.charAt(0) ?? 'A'}
              </div>
              <div className="text-left">
                <span className="block text-gray-900">{post.author}</span>
              </div>
            </div>
            <div className="hidden sm:block w-px h-8 bg-gray-200"></div>
            <div className="flex gap-6">
              <span className="flex items-center gap-2"><Calendar size={16} /> {post.date}</span>
            </div>
          </div>
        </header>

        {/* Featured Image */}
        {post.image && (
          <div className="max-w-6xl mx-auto px-4 mb-16">
            <div className="relative aspect-[16/9] md:aspect-[21/9] rounded-[2rem] overflow-hidden shadow-2xl">
              <img src={post.image} alt={post.title} className="absolute inset-0 w-full h-full object-cover" />
            </div>
          </div>
        )}

        {/* Main Content Layout */}
        <div className="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-12">
          
          {/* Sidebar (Share) */}
          <aside className="hidden lg:block col-span-3 pl-8">
            <div className="sticky top-24 space-y-8">
              <div className="flex flex-col gap-4">
                <span className="text-xs font-bold text-gray-400 uppercase tracking-widest">Share</span>
                <button 
                  onClick={() => window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`, '_blank')}
                  className="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm">
                  <Facebook size={18} />
                </button>
                <button 
                  onClick={() => window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(post.title)}`, '_blank')}
                  className="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-600 hover:text-sky-500 hover:border-sky-200 transition-all shadow-sm">
                  <Twitter size={18} />
                </button>
                <button 
                  onClick={() => window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}`, '_blank')}
                  className="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-600 hover:text-blue-700 hover:border-blue-200 transition-all shadow-sm">
                  <Linkedin size={18} />
                </button>
                <div className="relative">
                  {copied && (
                    <div className="absolute -top-4 left-1/4 -translate-x-1/2 bg-gray-900 text-white text-xs font-bold px-2 py-1 rounded-lg whitespace-nowrap">
                      Copied!
                    </div>
                  )}
                  <button
                    onClick={handleCopyLink}
                    title={copied ? 'Copied!' : 'Copy link'}
                    className={`w-10 h-10 rounded-full bg-white border flex items-center justify-center transition-all shadow-sm
                      ${copied 
                        ? 'border-green-300 text-[#2E7D32] bg-green-50' 
                        : 'border-gray-100 text-gray-600 hover:text-[#2E7D32] hover:border-green-200'
                      }`}
                  >
                    {copied ? <span className="text-xs font-bold">✓</span> : <LinkIcon size={18} />}
                  </button>
                </div>
              </div>
            </div>
          </aside>

          {/* Article Content */}
          <div className="col-span-1 lg:col-span-7">
            <div 
              className="prose prose-lg prose-green max-w-none [&>p]:text-gray-600 [&>p]:leading-8 [&>p]:mb-6 [&>h3]:text-2xl [&>h3]:font-black [&>h3]:font-serif [&>h3]:text-gray-900 [&>h3]:mt-10 [&>h3]:mb-4 [&>blockquote]:border-l-4 [&>blockquote]:border-[#2E7D32] [&>blockquote]:pl-6 [&>blockquote]:italic [&>blockquote]:text-gray-700 [&>blockquote]:my-8 [&>blockquote]:text-xl"
              dangerouslySetInnerHTML={{ __html: post.content }} 
            />

            {/* Tags */}
            {post.tags && post.tags.length > 0 && (
              <div className="mt-12 pt-8 border-t border-gray-200 flex flex-wrap gap-2">
                {post.tags.map(tag => (
                  <span key={tag} className="px-3 py-1 bg-gray-100 text-gray-600 text-sm font-bold rounded-lg cursor-pointer hover:bg-gray-200 transition-colors">
                    #{tag}
                  </span>
                ))}
              </div>
            )}
          </div>

          <div className="col-span-2"></div>
        </div>
      </article>
    </AppHeaderLayout>
  );
}