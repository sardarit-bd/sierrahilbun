import AppLayout from "@/layouts/app-layout";
import { Head } from "@inertiajs/react";
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Soil',
        href: '/soil',
    },
];

export default function Soil() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Soil Test" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                
            </div>
        </AppLayout>
    );
}