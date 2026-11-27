<?php

/*
|--------------------------------------------------------------------------
| Editable public-page wording
|--------------------------------------------------------------------------
| Every field below can be changed by a super admin under
| Admin > Page Content > Page text. The "default" is what shows until
| someone edits it, and what "Reset to default" restores.
|
| Page keys match the slide/card page names. Field types: text | textarea.
| "*_highlight" fields are the hand-drawn underlined words in a heading.
*/

return [
    'pages' => [
        'home' => [
            'label' => 'Home',
            'route' => 'home',
            'fields' => [
                'hero_kicker' => ['group' => 'Hero', 'label' => 'Small label above the heading', 'default' => 'Church of Uganda Youth'],
                'hero_title' => ['group' => 'Hero', 'label' => 'Heading', 'default' => 'Faith that fits'],
                'hero_title_highlight' => ['group' => 'Hero', 'label' => 'Heading: underlined words', 'default' => 'your life.'],
                'hero_intro' => ['group' => 'Hero', 'label' => 'Intro', 'type' => 'textarea', 'default' => 'Daily Scripture on your phone. Courses you can finish between classes. Events worth showing up for, and a life group where people actually know your name.'],
                'hero_guest_primary' => ['group' => 'Hero', 'label' => 'Main button (signed out)', 'default' => 'Create Youth Account'],
                'hero_guest_secondary' => ['group' => 'Hero', 'label' => 'Second button (signed out)', 'default' => 'I already have one'],
                'hero_member_primary' => ['group' => 'Hero', 'label' => 'Main button (signed in)', 'default' => 'Go to my space'],
                'hero_member_secondary' => ['group' => 'Hero', 'label' => 'Second button (signed in)', 'default' => 'Browse courses'],
                'paths_title' => ['group' => 'Start wherever you are', 'label' => 'Section heading', 'default' => 'Start wherever you are'],
                'path_events_title' => ['group' => 'Start wherever you are', 'label' => 'Events tile: heading', 'default' => 'Show up'],
                'path_events_body' => ['group' => 'Start wherever you are', 'label' => 'Events tile: text', 'type' => 'textarea', 'default' => 'Worship nights, camps, conferences and missions. Come along, and bring a friend.'],
                'path_events_link' => ['group' => 'Start wherever you are', 'label' => 'Events tile: link text', 'default' => 'See events'],
                'path_courses_title' => ['group' => 'Start wherever you are', 'label' => 'Courses tile: heading', 'default' => 'Go deeper'],
                'path_courses_body' => ['group' => 'Start wherever you are', 'label' => 'Courses tile: text', 'type' => 'textarea', 'default' => 'Short Bible studies and life-skills courses. Do a lesson on the taxi home.'],
                'path_courses_link' => ['group' => 'Start wherever you are', 'label' => 'Courses tile: link text', 'default' => 'Browse courses'],
                'path_churches_title' => ['group' => 'Start wherever you are', 'label' => 'Churches tile: heading', 'default' => 'Find your church'],
                'path_churches_body' => ['group' => 'Start wherever you are', 'label' => 'Churches tile: text', 'type' => 'textarea', 'default' => 'See which churches near you have a youth fellowship, and when they meet.'],
                'path_churches_link' => ['group' => 'Start wherever you are', 'label' => 'Churches tile: link text', 'default' => 'Search the map'],
                'events_title' => ['group' => 'Sections', 'label' => 'Events: heading', 'default' => 'Coming up'],
                'events_intro' => ['group' => 'Sections', 'label' => 'Events: intro', 'default' => 'Save the date.'],
                'news_title' => ['group' => 'Sections', 'label' => 'Stories: heading', 'default' => 'Stories & devotionals'],
                'news_intro' => ['group' => 'Sections', 'label' => 'Stories: intro', 'default' => 'From young people and leaders around the church.'],
                'cards_title' => ['group' => 'Sections', 'label' => 'Cards: heading', 'default' => "Don't miss"],
                'join_title' => ['group' => 'Join banner', 'label' => 'Heading', 'default' => "There's a seat saved for you."],
                'join_body' => ['group' => 'Join banner', 'label' => 'Text', 'type' => 'textarea', 'default' => "It's free. Make an account to keep your courses, sign up for events and get a fresh verse every morning."],
                'join_guest_button' => ['group' => 'Join banner', 'label' => 'Button (signed out)', 'default' => 'Join free'],
                'join_member_button' => ['group' => 'Join banner', 'label' => 'Button (signed in)', 'default' => 'Open my space'],
            ],
        ],

        'about' => [
            'label' => 'About',
            'route' => 'public.about',
            'fields' => [
                'hero_kicker' => ['group' => 'Hero', 'label' => 'Small label above the heading', 'default' => 'Church of Uganda Youth Ministry'],
                'hero_title' => ['group' => 'Hero', 'label' => 'Heading', 'default' => 'About COU Youth'],
                'hero_intro' => ['group' => 'Hero', 'label' => 'Intro', 'type' => 'textarea', 'default' => "COU Youth is the Church of Uganda's online home for young people: a place to grow in faith, make friends, find support and discover where you can serve."],
                'hero_primary' => ['group' => 'Hero', 'label' => 'Main button', 'default' => 'Explore Courses'],
                'hero_secondary' => ['group' => 'Hero', 'label' => 'Second button', 'default' => 'View Events'],
                'intro_title' => ['group' => 'Hero', 'label' => 'Side card: heading', 'default' => 'Connecting young people'],
                'intro_body' => ['group' => 'Hero', 'label' => 'Side card: text', 'type' => 'textarea', 'default' => 'The platform helps young people discover church programmes, discipleship resources, events, opportunities, pastoral support and safe ways to participate in ministry.'],
                'features_title' => ['group' => "What you'll find here", 'label' => 'Section heading', 'default' => "What you'll find here"],
                'features_intro' => ['group' => "What you'll find here", 'label' => 'Section intro', 'default' => 'Everything here is built to help you grow, belong and make a difference.'],
                'feature_1_title' => ['group' => "What you'll find here", 'label' => 'Feature 1: heading', 'default' => 'Discipleship'],
                'feature_1_body' => ['group' => "What you'll find here", 'label' => 'Feature 1: text', 'type' => 'textarea', 'default' => 'Courses, Bible studies and devotionals to help you know God better, one step at a time.'],
                'feature_2_title' => ['group' => "What you'll find here", 'label' => 'Feature 2: heading', 'default' => 'Community'],
                'feature_2_body' => ['group' => "What you'll find here", 'label' => 'Feature 2: text', 'type' => 'textarea', 'default' => 'Events, life groups and fellowships where you can meet people who will walk the journey with you.'],
                'feature_3_title' => ['group' => "What you'll find here", 'label' => 'Feature 3: heading', 'default' => 'Safeguarding'],
                'feature_3_body' => ['group' => "What you'll find here", 'label' => 'Feature 3: text', 'type' => 'textarea', 'default' => "Your safety matters. We ask for a parent or guardian's consent where it's needed, and our team keeps an eye on what's shared so everyone can take part safely."],
                'feature_4_title' => ['group' => "What you'll find here", 'label' => 'Feature 4: heading', 'default' => 'Church Locator'],
                'feature_4_body' => ['group' => "What you'll find here", 'label' => 'Feature 4: text', 'type' => 'textarea', 'default' => 'Moving to a new town or just visiting? Find a church nearby and see when the youth meet.'],
                'feature_5_title' => ['group' => "What you'll find here", 'label' => 'Feature 5: heading', 'default' => 'Giving'],
                'feature_5_body' => ['group' => "What you'll find here", 'label' => 'Feature 5: text', 'type' => 'textarea', 'default' => 'Give safely and securely to youth ministry campaigns you care about.'],
                'feature_6_title' => ['group' => "What you'll find here", 'label' => 'Feature 6: heading', 'default' => 'Mobile Access'],
                'feature_6_body' => ['group' => "What you'll find here", 'label' => 'Feature 6: text', 'type' => 'textarea', 'default' => 'Use the mobile app or the website on any phone, tablet or computer, wherever you are.'],
                'cards_title' => ['group' => 'Highlights', 'label' => 'Cards: heading', 'default' => 'Highlights'],
                'cards_intro' => ['group' => 'Highlights', 'label' => 'Cards: intro', 'default' => 'More about youth ministry in the Church of Uganda.'],
                'cta_title' => ['group' => 'Join banner', 'label' => 'Heading', 'default' => 'Be part of the COU Youth community'],
                'cta_body' => ['group' => 'Join banner', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Create a free account to save your progress, sign up for events and find your place in the church family.'],
            ],
        ],

        'news' => [
            'label' => 'News & Resources',
            'route' => 'public.news',
            'fields' => [
                'hero_title' => ['label' => 'Heading', 'default' => 'Stories &'],
                'hero_title_highlight' => ['label' => 'Heading: underlined words', 'default' => 'resources'],
                'hero_intro' => ['label' => 'Intro', 'type' => 'textarea', 'default' => 'Devotionals, stories and opportunities from youth ministry across the Church of Uganda.'],
                'empty' => ['label' => 'Message when there is nothing to show', 'default' => 'Nothing here yet. Check back soon for new stories and devotionals.'],
            ],
        ],

        'events' => [
            'label' => 'Events',
            'route' => 'public.events',
            'fields' => [
                'hero_kicker' => ['label' => 'Small label above the heading', 'default' => 'YOUTH COMMUNITY'],
                'hero_title' => ['label' => 'Heading', 'default' => "What's"],
                'hero_title_highlight' => ['label' => 'Heading: underlined words', 'default' => 'on'],
                'hero_intro' => ['label' => 'Intro', 'type' => 'textarea', 'default' => 'Worship nights, conferences, missions and fellowships happening across Uganda. Come along, and bring a friend.'],
                'empty_title' => ['label' => 'No events: heading', 'default' => 'No upcoming events just now'],
                'empty_body' => ['label' => 'No events: text', 'default' => 'New events are added all the time, so check back soon.'],
            ],
        ],

        'courses' => [
            'label' => 'Courses',
            'route' => 'public.courses',
            'fields' => [
                'hero_kicker' => ['label' => 'Small label above the heading', 'default' => 'GROW IN FAITH'],
                'hero_title' => ['label' => 'Heading', 'default' => 'Courses to help you'],
                'hero_title_highlight' => ['label' => 'Heading: underlined words', 'default' => 'grow'],
                'hero_intro' => ['label' => 'Intro', 'type' => 'textarea', 'default' => 'Bible studies and discipleship courses you can work through at your own pace, on your own, with friends or with your life group.'],
                'empty_title' => ['label' => 'No courses: heading', 'default' => 'No courses yet'],
                'empty_body' => ['label' => 'No courses: text', 'default' => 'New courses are on the way, so check back soon.'],
            ],
        ],

        'life-groups' => [
            'label' => 'Life Groups',
            'route' => 'public.life-groups',
            'fields' => [
                'hero_kicker' => ['label' => 'Small label above the heading', 'default' => 'BELONG & GROW'],
                'hero_title' => ['label' => 'Heading', 'default' => 'Life Groups: find your'],
                'hero_title_highlight' => ['label' => 'Heading: underlined words', 'default' => 'people'],
                'hero_intro' => ['label' => 'Intro', 'type' => 'textarea', 'default' => 'Find a youth fellowship or small group where you can build friendships, study Scripture, pray together and serve your community.'],
                'empty_title' => ['label' => 'No groups: heading', 'default' => 'No life groups found'],
                'empty_body' => ['label' => 'No groups: text', 'default' => 'Try a different search, or check back as new groups start.'],
            ],
        ],

        'churches' => [
            'label' => 'Church Locator',
            'route' => 'public.churches',
            'fields' => [
                'hero_kicker' => ['label' => 'Small label above the heading', 'default' => 'Church of Uganda'],
                'hero_title' => ['label' => 'Heading', 'default' => 'Find a church'],
                'hero_title_highlight' => ['label' => 'Heading: underlined words', 'default' => 'near you'],
                'hero_intro' => ['label' => 'Intro', 'type' => 'textarea', 'default' => 'Looking for a church? Find one near you, see how to get in touch and when the youth fellowship meets.'],
                'empty_title' => ['label' => 'No results: heading', 'default' => 'No church locations found'],
                'empty_body' => ['label' => 'No results: text', 'default' => 'Try a different church name, town or area.'],
                'cards_title' => ['label' => 'Cards: heading', 'default' => 'Featured'],
                'cards_intro' => ['label' => 'Cards: intro', 'default' => 'Helpful things to know before you visit.'],
            ],
        ],

        'donate' => [
            'label' => 'Donate',
            'route' => 'public.donate',
            'fields' => [
                'hero_kicker' => ['group' => 'Hero', 'label' => 'Small label above the heading', 'default' => 'Giving'],
                'hero_title' => ['group' => 'Hero', 'label' => 'Heading', 'default' => 'Support Youth Ministry'],
                'hero_intro' => ['group' => 'Hero', 'label' => 'Intro', 'type' => 'textarea', 'default' => 'Your gift helps young people across Uganda grow in faith, reach out to their communities and discover their calling.'],
                'message_title' => ['group' => 'Hero', 'label' => 'Side card: heading', 'default' => 'Your support makes a difference'],
                'message_body' => ['group' => 'Hero', 'label' => 'Side card: text', 'type' => 'textarea', 'default' => 'Every contribution helps strengthen youth discipleship, participation, outreach and ministry opportunities.'],
                'campaigns_title' => ['group' => 'Campaigns', 'label' => 'Heading', 'default' => 'Active Campaigns'],
                'campaigns_intro' => ['group' => 'Campaigns', 'label' => 'Intro', 'default' => "Choose something that's close to your heart."],
                'empty_title' => ['group' => 'Campaigns', 'label' => 'No campaigns: heading', 'default' => 'No active donation campaigns'],
                'empty_body' => ['group' => 'Campaigns', 'label' => 'No campaigns: text', 'default' => "There's nothing to give to right now. Please check back soon."],
                'cards_title' => ['group' => 'Cards & closing note', 'label' => 'Cards: heading', 'default' => 'Featured'],
                'cards_intro' => ['group' => 'Cards & closing note', 'label' => 'Cards: intro', 'default' => 'See how your giving makes a difference.'],
                'secure_title' => ['group' => 'Cards & closing note', 'label' => 'Closing note: heading', 'default' => 'Give securely'],
                'secure_body' => ['group' => 'Cards & closing note', 'label' => 'Closing note: text', 'type' => 'textarea', 'default' => "Sign in to give through a secure payment option. We'll send you a confirmation once your gift goes through."],
            ],
        ],

        'prayer' => [
            'label' => 'Prayer',
            'route' => 'public.prayer',
            'fields' => [
                'hero_kicker' => ['group' => 'Hero', 'label' => 'Small label above the heading', 'default' => 'PRAYER & CARE'],
                'hero_title' => ['group' => 'Hero', 'label' => 'Heading', 'default' => "You don't have to carry it"],
                'hero_title_highlight' => ['group' => 'Hero', 'label' => 'Heading: underlined words', 'default' => 'alone'],
                'hero_intro' => ['group' => 'Hero', 'label' => 'Intro', 'type' => 'textarea', 'default' => "Exams, family, money, faith, anything. Share a prayer request with our youth pastoral team and see how they're following up."],
                'form_title' => ['group' => 'Request form', 'label' => 'Form heading', 'default' => 'Submit a prayer request'],
                'form_intro' => ['group' => 'Request form', 'label' => 'Form intro', 'default' => 'You decide who can see your request.'],
                'privacy_note' => ['group' => 'Request form', 'label' => 'Safety note under the form', 'type' => 'textarea', 'default' => 'Do not include passwords, financial PINs or other secrets. For immediate danger or emergencies, contact local emergency or trusted safeguarding support directly.'],
                'care_title' => ['group' => 'Pastoral card', 'label' => 'Heading', 'default' => 'You are not alone'],
                'care_body' => ['group' => 'Pastoral card', 'label' => 'Text', 'type' => 'textarea', 'default' => "Members of our pastoral team read requests and pray for you. When it would help, they'll follow up and leave you a note here."],
                'history_title' => ['group' => 'My requests', 'label' => 'Heading', 'default' => 'My prayer requests'],
                'empty_title' => ['group' => 'My requests', 'label' => 'No requests: heading', 'default' => 'No prayer requests yet'],
                'empty_body' => ['group' => 'My requests', 'label' => 'No requests: text', 'default' => "When you share a request, you'll see it here along with any updates."],
            ],
        ],

        'footer' => [
            'label' => 'Footer (all public pages)',
            'route' => 'home',
            'fields' => [
                'explore_title' => ['label' => 'Column 1 heading', 'default' => 'Explore'],
                'grow_title' => ['label' => 'Column 2 heading', 'default' => 'Grow & Connect'],
                'account_title' => ['label' => 'Column 3 heading', 'default' => 'Account & Support'],
                'copyright' => ['label' => 'Copyright line (the year is added in front)', 'default' => 'Church of Uganda. All rights reserved.'],
                'motto' => ['label' => 'Motto at the bottom', 'default' => 'Connect. Grow. Serve.'],
            ],
        ],
    ],
];
