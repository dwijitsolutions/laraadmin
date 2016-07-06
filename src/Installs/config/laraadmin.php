<?php
/**
 * Config genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

return [
    
    'sitename' => "LaraAdmin 1.0",
    'sitename2' => ["Lara", "Admin 1.0"],
    'sitedesc' => "LaraAdmin is a better and smoother way to manage Projects, Clients, Revenue and all the other aspects of Small & Medium Businesses.",
    
    'adminRoute' => 'admin',
    
    /*
    |--------------------------------------------------------------------------
    | Uploads Configuration
    |--------------------------------------------------------------------------
    |
    | private_uploads: Show that uploaded file remains private and can be seen by respective owners only
    | default_uploads_security: public / private
    | 
    */
    'uploads' => [
        'private_uploads' => false,     // Does Uploads are Private or Public
        'default_public' => false,      // Default Upload Visibility
        'allow_filename_change' => false// Necessory for SEO
    ],
];