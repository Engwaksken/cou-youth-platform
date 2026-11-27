<?php

return [
    'tagline' => 'Connecting Young People. Growing Disciples. Transforming Nations.',
    'supported_languages' => ['en', 'lg'],
    'default_currency' => 'UGX',
    'youth_categories' => [
        'teen' => [12, 17],
        'youth' => [18, 23],
        'young_adult' => [24, 35],
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily engagement
    |--------------------------------------------------------------------------
    |
    | Days for check-in streaks and greetings follow the members' local clock,
    | not the server's UTC clock, so a check-in at 1am Kampala time counts for
    | the right day.
    |
    */
    'engagement_timezone' => env('YOUTH_ENGAGEMENT_TIMEZONE', 'Africa/Kampala'),

    /*
    | One verse is shown per day, rotating through this list by day of the
    | year. Text: World English Bible, British Edition (public domain), so it
    | can be shown and shared freely. Swap in another list if the church
    | prefers a different translation it is licensed to reproduce.
    */
    'daily_verses_translation' => 'WEBBE',
    'daily_verses' => [
        ['ref' => '1 Timothy 4:12', 'text' => 'Let no man despise your youth; but be an example to those who believe, in word, in your way of life, in love, in spirit, in faith, and in purity.'],
        ['ref' => 'Psalm 119:105', 'text' => 'Your word is a lamp to my feet, and a light for my path.'],
        ['ref' => 'Philippians 4:13', 'text' => 'I can do all things through Christ, who strengthens me.'],
        ['ref' => 'Joshua 1:9', 'text' => 'Haven’t I commanded you? Be strong and courageous. Don’t be afraid. Don’t be dismayed, for the LORD your God is with you wherever you go.'],
        ['ref' => 'Proverbs 3:5–6', 'text' => 'Trust in the LORD with all your heart, and don’t lean on your own understanding. In all your ways acknowledge him, and he will make your paths straight.'],
        ['ref' => 'Isaiah 40:31', 'text' => 'But those who wait for the LORD will renew their strength. They will mount up with wings like eagles. They will run, and not be weary. They will walk, and not faint.'],
        ['ref' => 'Matthew 11:28', 'text' => 'Come to me, all you who labour and are heavily burdened, and I will give you rest.'],
        ['ref' => 'Romans 8:28', 'text' => 'We know that all things work together for good for those who love God, for those who are called according to his purpose.'],
        ['ref' => '2 Timothy 1:7', 'text' => 'For God didn’t give us a spirit of fear, but of power, love, and self-control.'],
        ['ref' => 'Lamentations 3:22–23', 'text' => 'It is because of the LORD’s loving kindnesses that we are not consumed, because his compassion doesn’t fail. They are new every morning. Great is your faithfulness.'],
        ['ref' => 'Psalm 119:9', 'text' => 'How can a young man keep his way pure? By living according to your word.'],
        ['ref' => 'John 15:5', 'text' => 'I am the vine. You are the branches. He who remains in me and I in him bears much fruit, for apart from me you can do nothing.'],
        ['ref' => 'Micah 6:8', 'text' => 'He has shown you, O man, what is good. What does the LORD require of you, but to act justly, to love mercy, and to walk humbly with your God?'],
        ['ref' => 'Galatians 6:9', 'text' => 'Let’s not be weary in doing good, for we will reap in due season if we don’t give up.'],
        ['ref' => 'Psalm 23:1', 'text' => 'The LORD is my shepherd; I shall lack nothing.'],
        ['ref' => 'Matthew 5:14', 'text' => 'You are the light of the world. A city located on a hill can’t be hidden.'],
        ['ref' => '1 Peter 5:7', 'text' => 'Casting all your worries on him, because he cares for you.'],
        ['ref' => 'Zephaniah 3:17', 'text' => 'The LORD, your God, is among you, a mighty one who will save. He will rejoice over you with joy. He will calm you in his love. He will rejoice over you with singing.'],
        ['ref' => 'Psalm 139:14', 'text' => 'I will give thanks to you, for I am fearfully and wonderfully made. Your works are wonderful. My soul knows that very well.'],
        ['ref' => 'John 13:34', 'text' => 'A new commandment I give to you, that you love one another. Just as I have loved you, you also love one another.'],
        ['ref' => 'Romans 12:2', 'text' => 'Don’t be conformed to this world, but be transformed by the renewing of your mind, so that you may prove what is the good, well-pleasing, and perfect will of God.'],
        ['ref' => 'Isaiah 41:10', 'text' => 'Don’t you be afraid, for I am with you. Don’t be dismayed, for I am your God. I will strengthen you. Yes, I will help you. Yes, I will uphold you with the right hand of my righteousness.'],
        ['ref' => 'Psalm 34:8', 'text' => 'Oh taste and see that the LORD is good. Blessed is the man who takes refuge in him.'],
        ['ref' => '1 John 4:19', 'text' => 'We love him, because he first loved us.'],
        ['ref' => 'Proverbs 27:17', 'text' => 'Iron sharpens iron; so a man sharpens his friend’s countenance.'],
        ['ref' => 'James 1:5', 'text' => 'But if any of you lacks wisdom, let him ask of God, who gives to all liberally and without reproach; and it will be given to him.'],
        ['ref' => 'Psalm 46:10', 'text' => 'Be still, and know that I am God. I will be exalted among the nations. I will be exalted in the earth.'],
        ['ref' => 'Colossians 3:23', 'text' => 'And whatever you do, work heartily, as for the Lord, and not for men.'],
        ['ref' => 'Jeremiah 29:11', 'text' => 'For I know the thoughts that I think towards you, says the LORD, thoughts of peace, and not of evil, to give you hope and a future.'],
        ['ref' => 'Hebrews 10:24', 'text' => 'Let’s consider how to provoke one another to love and good works.'],
    ],
];

// Phase 5 production services are configured through config/services.php and environment variables.
