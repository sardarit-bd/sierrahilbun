import React from 'react';
import { 
    CheckCircle2, 
    ClipboardEdit, 
    Leaf, 
    Droplets, 
    Bug, 
    CalendarDays, 
    ChevronRight, 
    Sprout,
    CloudRain
} from 'lucide-react';

import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

export default function Dashboard({ auth }) {
    const breadcrumbs = [{ title: 'Dashboard', href: '#' }];
    const userName = auth?.user?.name || 'Alex';


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            
            
            <div className="flex flex-col gap-6 md:gap-8 p-4">
                
                {/* Welcome Section */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                            Welcome back, {userName}
                        </h1>
                    </div>
                </div>

                {/* Main Action Cards (From Screenshot) */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    
                    {/* Card 1: Lawn Plan */}
                    <div className="bg-[#2a7d4d] rounded-2xl p-6 md:p-8 flex flex-col justify-between shadow-md relative overflow-hidden group hover:shadow-lg transition-all duration-300 min-h-[220px]">
                        {/* Decorative background flare */}
                        <div className="absolute -top-12 -right-12 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl group-hover:opacity-20 transition-opacity duration-500 pointer-events-none"></div>
                        
                        <div className="relative z-10">
                            <div className="mb-5 inline-flex items-center justify-center w-10 h-10 bg-white rounded-full shadow-sm">
                                <CheckCircle2 className="w-6 h-6 text-[#2a7d4d]" strokeWidth={2.5} />
                            </div>
                            <h2 className="text-2xl md:text-[28px] font-extrabold text-white leading-tight mb-6">
                                Your custom lawn<br />plan is ready!
                            </h2>
                        </div>
                        
                        <div className="relative z-10 mt-auto">
                            <button className="bg-[#ffbc54] hover:bg-[#f2b047] active:scale-95 text-[#0d3644] font-bold text-base py-3 px-7 rounded-lg transition-all shadow-sm w-fit inline-flex items-center gap-2">
                                See my plan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}