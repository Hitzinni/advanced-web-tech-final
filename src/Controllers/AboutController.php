<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;

class AboutController
{
    public function index(): void
    {
        $pageTitle = 'About Us | Online Grocery Store';
        $metaDescription = 'Learn about our grocery store, our mission, values, and the team behind our high-quality products and service.';
        
        // Team members information
        $teamMembers = [
            [
                'name' => 'Jane Smith',
                'position' => 'Founder & CEO',
                'bio' => 'Jane founded our grocery store in 2015 with a vision to provide fresh, locally-sourced products to the community. She has over 15 years of experience in the food industry.',
                'image' => 'images/team/cartoon-ceo.jpg'
            ],
            [
                'name' => 'Michael Johnson',
                'position' => 'Head of Operations',
                'bio' => 'Michael oversees all daily operations to ensure that our customers receive the highest quality products. He has a background in supply chain management and food safety.',
                'image' => 'images/team/cartoon-operations.jpg'
            ],
            [
                'name' => 'Emily Chen',
                'position' => 'Lead Nutritionist',
                'bio' => 'Emily helps us select healthy, nutritious products and provides expert advice to our customers. She has a degree in Nutrition and has worked with us since 2018.',
                'image' => 'images/team/cartoon-nutritionist.jpg'
            ],
            [
                'name' => 'David Wilson',
                'position' => 'Customer Service Manager',
                'bio' => 'David ensures that every customer has a pleasant shopping experience. His team is trained to assist you with any questions or concerns you might have.',
                'image' => 'images/team/cartoon-customer-service.jpg'
            ]
        ];
        
        // Company milestones
        $milestones = [
            [
                'year' => '2015',
                'title' => 'Founded',
                'description' => 'Our grocery store was established with a mission to provide fresh, quality products to the local community.'
            ],
            [
                'year' => '2017',
                'title' => 'Online Platform Launch',
                'description' => 'We launched our online platform to make shopping more convenient for our customers.'
            ],
            [
                'year' => '2019',
                'title' => 'Expanded Product Range',
                'description' => 'Added over 500 new products to our inventory, including organic and specialty items.'
            ],
            [
                'year' => '2021',
                'title' => 'Sustainable Packaging Initiative',
                'description' => 'Implemented eco-friendly packaging across all our products and delivery services.'
            ],
            [
                'year' => '2023',
                'title' => 'Mobile App Release',
                'description' => 'Released our mobile app to provide an even more seamless shopping experience.'
            ]
        ];
        
        View::output('about', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'teamMembers' => $teamMembers,
            'milestones' => $milestones
        ]);
    }
} 