<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->slug,
            'title'    => $this->title,
            'type'     => $this->type,
            'hasMore'  => $this->activeOptions->where('is_more', true)->isNotEmpty(),
            'options'  => $this->activeOptions->map(fn ($opt) => [
                'id'          => $opt->slug,
                'label'       => $opt->label,
                'img'         => $opt->image_url,
                'tag'         => $opt->tag,
                'desc'        => $opt->description,
                'recommended' => $opt->is_recommended,
                'isMore'      => $opt->is_more,
            ]),
            'moreOptions' => $this->activeOptions
                ->where('is_more', true)
                ->values()
                ->map(fn ($opt) => [
                    'id'          => $opt->slug,
                    'label'       => $opt->label,
                    'img'         => $opt->image_url,
                    'tag'         => $opt->tag,
                    'desc'        => $opt->description,
                    'recommended' => $opt->is_recommended,
                    'isMore'      => true,
                ]),
        ];
    }
}