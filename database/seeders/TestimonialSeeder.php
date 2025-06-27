<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run()
    {
        $testimonials = [
            [
                'name' => 'Ahmed Mohamed',
                'email' => 'ahmed.mohamed@example.com',
                'role' => 'Software Developer at Valeo',
                'message' => 'The ITI program gave me the exact skills needed to land my dream job at Valeo.',
                'rating' => 5,
                'status' => 'approved'
            ],
            [
                'name' => 'Fatma Ali',
                'email' => 'fatma.ali@example.com',
                'role' => 'UI/UX Designer at ITWorx',
                'message' => 'Excellent platform with quality job listings specifically for ITI graduates.',
                'rating' => 5,
                'status' => 'approved'
            ],
            [
                'name' => 'Mohamed Saad',
                'email' => 'mohamed.saad@example.com',
                'role' => 'Network Engineer at Raya',
                'message' => 'Love the user experience and the variety of tech opportunities available for ITI alumni.',
                'rating' => 5,
                'status' => 'approved'
            ]
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}