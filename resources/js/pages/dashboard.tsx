import React from 'react';
import {
    CheckCircle2, User, Calendar, ChevronRight,
    Clock, ArrowUpRight, Leaf, Droplets, Sprout,
    ShieldCheck, Mail, Package, Truck
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, usePage } from '@inertiajs/react';
import ArticleCard from '@/components/front/dashboard/article-card';

const DeliveryBadge = ({ status }: { status: string }) => {
    const map: Record<string, string> = {
        delivered:  'bg-green-100 text-green-700',
        shipped:    'bg-blue-100 text-blue-700',
        processing: 'bg-orange-100 text-orange-700',
        pending:    'bg-slate-100 text-slate-600',
    };
    return (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${map[status?.toLowerCase()] ?? map.pending}`}>
            {status ?? 'Pending'}
        </span>
    );
};

export default function Dashboard() {
    const { user, stats, recent_orders, articles } = usePage<{
        user:          { name: string; email: string; created_at: string };
        stats:         { total_orders: number };
        recent_orders: any[];
        articles:      any[];
    }>().props;

    return (
        <AppLayout>
            <Head title="Dashboard" />
            <div className="min-h-screen bg-slate-50 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 font-sans">
                <main className="mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

                    {/* Header */}
                    <header className="mb-10">
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 className="text-3xl md:text-4xl font-black tracking-tight text-neutral-900 dark:text-white mb-2">
                                    Welcome back, <span className="text-emerald-700 dark:text-emerald-500">{user.name}</span>
                                </h1>
                                <p className="text-neutral-500 dark:text-neutral-400 text-lg">
                                    Here's what's happening with your lawn today.
                                </p>
                            </div>
                            {/* <div className="flex items-center gap-3">
                                <div className="h-12 w-12 rounded-full bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex items-center justify-center shadow-sm">
                                    <User className="w-6 h-6 text-neutral-600 dark:text-neutral-300" />
                                </div>
                            </div> */}
                        </div>
                    </header>

                    {/* Top Row */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">

                        {/* Card 1: Lawn Plan CTA */}
                        <div className="group relative bg-[#2a7d4d] rounded-[2rem] p-8 md:p-10 flex flex-col justify-between shadow-2xl shadow-emerald-900/20 overflow-hidden transition-all duration-500 hover:translate-y-[-4px]">
                            <div className="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl group-hover:bg-emerald-400/30 transition-colors duration-500" />
                            <div className="absolute bottom-0 left-0 -ml-8 -mb-8 w-32 h-32 bg-white/5 rounded-full blur-xl" />
                            <div className="relative z-10">
                                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white/90 text-xs font-bold uppercase tracking-wider mb-6">
                                    <Leaf className="w-3 h-3" /> Make Plan
                                </div>
                                <h2 className="text-3xl md:text-4xl font-extrabold text-white leading-tight mb-4">
                                    Do you want<br />custom lawn plan?
                                </h2>
                                <p className="text-emerald-50/80 max-w-xs mb-8">
                                    We'll analyze your soil data and local climate to create your custom lawn plan.
                                </p>
                            </div>
                            <div className="relative z-10 mt-auto flex items-center justify-between">
                                <Link href="/custom-lawn">
                                    <button className="bg-white hover:bg-white/90 text-[#0d3644] font-bold text-base py-4 px-8 rounded-2xl transition-all duration-300 shadow-xl shadow-black/10 active:scale-95 flex items-center gap-3 group/btn cursor-pointer">
                                        Build your plan
                                        <ChevronRight className="w-5 h-5 group-hover/btn:translate-x-1 transition-transform" />
                                    </button>
                                </Link>
                                <div className="hidden sm:block">
                                    <div className="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20">
                                        <CheckCircle2 className="w-8 h-8 text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Card 2: Account Info */}
                        <div className="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-[2rem] p-8 md:p-10 flex flex-col shadow-sm transition-all duration-500 hover:shadow-xl">
                            <div className="flex justify-between items-start mb-8">
                                <div className="h-14 w-14 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                                    <User className="w-7 h-7 text-neutral-600 dark:text-neutral-300" />
                                </div>
                                <Link href="/settings/profile">
                                    <button className="p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-xl transition-colors">
                                        <ArrowUpRight className="w-5 h-5 text-neutral-400" />
                                    </button>
                                </Link>
                            </div>

                            <div>
                                <h3 className="text-sm font-bold text-neutral-400 uppercase tracking-[0.2em] mb-2">User Details</h3>
                                <div className="space-y-6">
                                    <p className="text-2xl font-bold text-neutral-900 dark:text-white">{user.name}</p>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="bg-neutral-50 dark:bg-neutral-800/50 p-4 rounded-2xl border border-neutral-100 dark:border-neutral-700/50">
                                            <div className="flex items-center gap-2 text-neutral-500 text-xs font-medium mb-1">
                                                <Calendar className="w-3.5 h-3.5" /> Since
                                            </div>
                                            <p className="font-bold text-neutral-800 dark:text-neutral-200 truncate">{user.created_at}</p>
                                        </div>
                                        <div className="bg-neutral-50 dark:bg-neutral-800/50 p-4 rounded-2xl border border-neutral-100 dark:border-neutral-700/50">
                                            <div className="flex items-center gap-2 text-neutral-500 text-xs font-medium mb-1">
                                                <Mail className="w-3.5 h-3.5" /> Email
                                            </div>
                                            <p className="font-bold text-emerald-600 dark:text-emerald-400 truncate text-sm">{user.email}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-auto pt-8">
                                <Link href="/settings/profile">
                                    <button className="w-full py-4 px-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-all active:scale-[0.98] cursor-pointer">
                                        Manage Account Settings
                                    </button>
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Articles */}
                    <ArticleCard articles={articles} />
                </main>
            </div>
        </AppLayout>
    );
}