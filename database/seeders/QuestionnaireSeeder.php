<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->data() as $sort => $questionData) {
            $question = Question::updateOrCreate(
                ['slug' => $questionData['slug']],
                [
                    'title'      => $questionData['title'],
                    'type'       => $questionData['type'] ?? 'single',
                    'sort_order' => $sort,
                    'is_active'  => true,
                ]
            );

            foreach ($questionData['options'] as $optSort => $opt) {
                $question->options()->updateOrCreate(
                    ['slug' => $opt['slug']],
                    [
                        'label'          => $opt['label'],
                        'image_url'      => $opt['image_url'] ?? null,
                        'tag'            => $opt['tag'] ?? null,
                        'description'    => $opt['description'] ?? null,
                        'is_recommended' => $opt['is_recommended'] ?? false,
                        'is_more'        => $opt['is_more'] ?? false,
                        'sort_order'     => $optSort,
                        'is_active'      => true,
                    ]
                );
            }
        }
    }

    private function data(): array
    {
        return [
            [
                'slug'  => 'goals',
                'title' => "What's important to you?",
                'options' => [
                    ['slug' => 'looks',  'label' => 'A great-looking lawn',            'image_url' => 'https://images.unsplash.com/photo-1558904541-efa843a96f01?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'health', 'label' => 'Healthy soil and healthy grass',  'image_url' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'safety', 'label' => 'Safe for people, pets, and nature', 'image_url' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'all',    'label' => 'All of the above!',                'image_url' => 'https://images.unsplash.com/reserve/Af0sF2OS5S5gatqrKzVP_Silhoutte.jpg?auto=format&fit=crop&q=80&w=400'],
                ],
            ],
            [
                'slug'  => 'pets',
                'title' => 'How often are pets on your lawn?',
                'options' => [
                    ['slug' => 'lot',      'label' => 'A lot',    'image_url' => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'not_much', 'label' => 'Not much', 'image_url' => 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&q=80&w=400'],
                ],
            ],
            [
                'slug'  => 'knowledge',
                'title' => 'Rate your lawn care knowledge.',
                'options' => [
                    ['slug' => 'expert',   'label' => 'Expert',   'image_url' => 'https://images.unsplash.com/photo-1501520158826-76df880863a3?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'hobbyist', 'label' => 'Hobbyist', 'image_url' => 'https://images.unsplash.com/photo-1527840330704-143cde6c566e?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'amateur',  'label' => 'Amateur',  'image_url' => 'https://images.unsplash.com/photo-1608101854678-b45ad1d25556?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'rookie',   'label' => 'Rookie',   'image_url' => 'https://images.unsplash.com/photo-1629193434016-60c866628d22?auto=format&fit=crop&q=80&w=400'],
                ],
            ],
            [
                'slug'  => 'grass',
                'title' => 'Confirm your grass type.',
                'options' => [
                    ['slug' => 'bermuda',   'label' => 'Bermudagrass',   'image_url' => 'https://images.unsplash.com/photo-1700547492500-92fbcc77fa70?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'centipede', 'label' => 'Centipedegrass', 'image_url' => 'https://images.unsplash.com/photo-1651177931602-17c680dd6782?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'augustine', 'label' => 'St. Augustine',  'image_url' => 'https://images.unsplash.com/photo-1533460004989-cef01064af7e?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'other',     'label' => 'Other/Not sure', 'image_url' => 'https://images.unsplash.com/photo-1606749482582-8c73563adc2b?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'zoysia',    'label' => 'Zoysiagrass',        'is_more' => true, 'image_url' => 'https://images.unsplash.com/photo-1540470174401-f25e9eb17c1c?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'fescue',    'label' => 'Fescue',             'is_more' => true, 'image_url' => 'https://images.unsplash.com/photo-1470137430626-983a37b8ea46?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'bluegrass', 'label' => 'Kentucky Bluegrass', 'is_more' => true, 'image_url' => 'https://images.unsplash.com/photo-1524491596574-ffd31af46beb?auto=format&fit=crop&q=80&w=400'],
                ],
            ],
            [
                'slug'  => 'patches',
                'title' => 'Does your lawn have any bare patches?',
                'options' => [
                    ['slug' => 'none',     'label' => 'Nope, none',      'image_url' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'few',      'label' => 'A few',           'image_url' => 'https://www.greenviewfertilizer.com/media/1646/brown-dead-bare-spot-on-lawn.jpg'],
                    ['slug' => 'moderate', 'label' => 'Moderate',        'image_url' => 'https://www.thespruce.com/thmb/fJXprM1KepAlHbv2J7eOjVbGa1A=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/what-are-dead-spots-on-lawn-2152737-01-8895e31ce33e4c5fa5c3d8a83ea11d6b.jpg'],
                    ['slug' => 'lots',     'label' => 'Lots of patches', 'image_url' => 'https://www.cobbittyturf.com.au/wp-content/uploads/2020/01/lawn-care-dry-spots.jpg'],
                ],
            ],
            [
                'slug'  => 'weeds',
                'title' => "What's your weed situation?",
                'options' => [
                    ['slug' => 'none',       'label' => "Weeds aren't an issue",             'image_url' => 'https://plus.unsplash.com/premium_photo-1664126702385-104edee258d7?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'leafy',      'label' => 'Leafy lawn weeds',                  'image_url' => 'https://images.unsplash.com/photo-1662559459212-d9cbe0276702?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'stubborn',   'label' => 'Patio, garden, and stubborn weeds', 'image_url' => 'https://images.unsplash.com/photo-1706611893334-c50aaca22dbf?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'everywhere', 'label' => 'Weeds are everywhere',              'image_url' => 'https://images.unsplash.com/photo-1527455505333-9d3ac7adf523?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'pre',        'label' => 'I want a pre-emergent',             'image_url' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&q=80&w=400'],
                ],
            ],
            [
                'slug'  => 'care',
                'title' => 'How do you care for your lawn?',
                'options' => [
                    ['slug' => 'service',   'label' => 'A lawn service does it all',           'image_url' => 'https://images.unsplash.com/photo-1746436576978-21632bf9790d?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'fert_high', 'label' => 'I fertilize three to five times a year', 'tag' => '3-5x', 'image_url' => 'https://images.unsplash.com/photo-1683316924890-6a8c5ab10d29?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'fert_low',  'label' => 'I fertilize once or twice a year',    'tag' => '1-2x', 'image_url' => 'https://images.unsplash.com/photo-1769000066710-40ac0c0ca2a6?auto=format&fit=crop&q=80&w=400'],
                    ['slug' => 'mow',       'label' => 'I just mow it',                        'image_url' => 'https://images.unsplash.com/photo-1590682680695-43b964a3ae17?auto=format&fit=crop&q=80&w=400'],
                ],
            ],
        ];
    }
}