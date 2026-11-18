<?php

return [
    'profiles' => ['display_name' => 'text', 'fullname' => 'text', 'email' => 'text', 'whatsapp' => 'text', 'contact_number' => 'text', 'linkedin_url' => 'text', 'instagram_url' => 'text', 'github_url' => 'text', 'facebook_url' => 'text', 'role' => 'text', 'location' => 'text', 'contact_location' => 'text', 'greeting' => 'text', 'page_title' => 'text', 'meta_description' => 'textarea', 'headshot' => 'text', 'photo' => 'text', 'photo_retina' => 'text', 'resume' => 'text', 'works_intro' => 'textarea', 'confidentiality' => 'textarea', 'contact_intro' => 'textarea', 'summary' => 'textarea', 'about' => 'textarea'],
    'expertise' => ['title' => 'text'],
    'skills' => ['title' => 'text', 'description' => 'textarea'],
    'experiences' => ['title' => 'text', 'organization' => 'text', 'period' => 'text', 'description' => 'textarea'],
    'education' => ['title' => 'text', 'organization' => 'text', 'period' => 'text', 'description' => 'textarea'],
    'tools' => ['title' => 'text', 'image' => 'text', 'appearance' => 'text'],
    'projects' => ['title' => 'text', 'category' => 'text', 'description' => 'textarea', 'tags' => 'text', 'image' => 'text', 'website_url' => 'text', 'contribution' => 'optional_textarea', 'technologies' => 'optional_text', 'approved' => 'approval'],
    'case_studies' => [
        'title' => 'text', 'description' => 'optional_textarea', 'problem' => 'optional_textarea', 'responsibilities' => 'optional_textarea',
        'technologies' => 'optional_text', 'architecture' => 'optional_textarea', 'challenges' => 'optional_textarea',
        'solutions' => 'optional_textarea', 'outcomes' => 'optional_textarea', 'confidentiality' => 'optional_textarea',
        'live_url' => 'optional_text', 'repository_url' => 'optional_text', 'screenshot_path' => 'optional_text',
        'screenshot_alt' => 'optional_text', 'approved' => 'approval',
    ],
];
