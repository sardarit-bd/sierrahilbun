import { BookOpen, ChevronRight, Clock } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface Article {
    id:           number;
    title:        string;
    slug:         string;
    excerpt:      string | null;
    category:     string | null;
    image_url:    string | null;
    image_alt:    string | null;
    published_at: string;
}

const SingleArticleCard = ({ article }: { article: Article }) => (
    <Link href={`/blog/${article.slug}`} className="group cursor-pointer block">
        <div className="relative aspect-[16/10] overflow-hidden rounded-[2rem] mb-5 shadow-lg shadow-neutral-200 dark:shadow-black/20">
            {article.image_url ? (
                <img
                    src={`storage/${article.image_url}`}
                    alt={article.image_alt ?? article.title}
                    className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
            ) : (
                <div className="w-full h-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                    <BookOpen className="w-10 h-10 text-emerald-300" />
                </div>
            )}
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
            {article.category && (
                <div className="absolute top-4 left-4">
                    <div className="bg-white/90 dark:bg-neutral-900/90 backdrop-blur px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                        <BookOpen className="w-3.5 h-3.5" />
                        {article.category}
                    </div>
                </div>
            )}
        </div>
        <div className="px-2">
            <div className="flex items-center gap-3 text-neutral-400 dark:text-neutral-500 text-xs font-bold mb-3 uppercase tracking-widest">
                <span className="flex items-center gap-1.5">
                    <Clock className="w-3.5 h-3.5" />
                    {article.published_at}
                </span>
            </div>
            <h3 className="text-xl font-extrabold text-neutral-900 dark:text-white leading-tight group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                {article.title}
            </h3>
            {article.excerpt && (
                <p className="text-sm text-neutral-500 dark:text-neutral-400 mt-2 line-clamp-2">
                    {article.excerpt}
                </p>
            )}
        </div>
    </Link>
);

export default function ArticleCard({ articles }: { articles: Article[] }) {
    if (!articles || articles.length === 0) return null;

    return (
        <section>
            <div className="flex items-end justify-between mb-8">
                <div>
                    <div className="inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-sm uppercase tracking-widest mb-2">
                        <BookOpen className="w-4 h-4" />
                        Learn & Grow
                    </div>
                    <h2 className="text-2xl md:text-3xl font-black text-neutral-900 dark:text-white">
                        Recommended for you
                    </h2>
                </div>
                <Link
                    href="/blogs"
                    className="hidden sm:flex items-center gap-2 text-neutral-500 hover:text-emerald-600 dark:hover:text-emerald-400 font-bold transition-colors"
                >
                    View All Articles
                    <ChevronRight className="w-5 h-5" />
                </Link>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {articles.map((article) => (
                    <SingleArticleCard key={article.id} article={article} />
                ))}
            </div>

            <Link
                href="/blogs"
                className="w-full mt-8 sm:hidden py-4 px-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 font-bold text-neutral-700 dark:text-neutral-300 flex items-center justify-center gap-2"
            >
                View All Articles
                <ChevronRight className="w-5 h-5" />
            </Link>
        </section>
    );
}