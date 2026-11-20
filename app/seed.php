<?php
// Sample data for the prototype. Every provider here is FICTIONAL and marked is_sample = 1,
// so the site shows a "Sample profile" label. Real providers are added only with their OK.
// Delete them all from the admin ("Remove sample data") before launch.

function seed_sample_data(PDO $pdo): void {
    $today = date('Y-m-d');
    $providers = [
        [
            'slug' => 'saddle-line-freediving', 'name' => 'Saddle Line Freediving', 'type' => 'school',
            'tagline' => 'Small groups, big calm. Daily lines at Lighthouse and the Blue Hole.',
            'bio' => "We are a small freediving school a two-minute walk from Lighthouse bay. Courses run in groups of three or fewer so every dive gets real feedback.\n\nMost students arrive for a beginner course and stay for a training week. We run morning line sessions every day and a Blue Hole day twice a week.",
            'languages' => 'English,Arabic,German', 'agencies' => 'AIDA,Molchanovs', 'licence' => 'centre',
            'whatsapp' => '+201000000001', 'instagram' => 'https://instagram.com/', 'website' => 'https://example.com/',
            'base_area' => 'Lighthouse', 'spots' => 'lighthouse,blue-hole,eel-garden', 'pool' => 0, 'max_ratio' => 3, 'gear_included' => 1,
            'years_teaching' => 9, 'students_certified' => 1400, 'personal_best' => 'CWT 78 m',
            'achievements' => "AIDA Instructor Trainer\n9 years teaching in Dahab\nSafety team at Blue Hole competitions",
            'teaching_week' => 1, 'week_note' => 'Beginner course starts Monday · 2 spots left', 'verified' => 1, 'featured' => 1, 'founding' => 1,
            'services' => [
                ['course', 0, 'AIDA', 'Discover freediving (1 day)', 1, 70, 'Pool-free intro in the bay'],
                ['course', 1, 'AIDA', 'AIDA 2 Freediver', 3, 260, 'Gear and certificate included'],
                ['course', 2, 'AIDA', 'AIDA 3 Advanced Freediver', 3, 320, ''],
                ['course', 3, 'Molchanovs', 'Molchanovs Wave 3', 4, 410, ''],
                ['coaching', 0, '', 'Coached line session', 0.5, 35, 'Certified freedivers'],
                ['rental', 0, '', 'Full freediving kit', 1, 10, 'Per day'],
            ],
        ],
        [
            'slug' => 'coach-lina-r', 'name' => 'Lina R.', 'type' => 'coach',
            'tagline' => 'Depth coaching for divers chasing 40 m and beyond.',
            'bio' => "I coach freedivers who already have a certificate and want to go deeper, safely. We film every dive and work on one thing at a time: mouthfill, freefall or relaxation.\n\nMonthly depth camps at the Blue Hole, max two athletes per coach.",
            'languages' => 'English,Italian,Spanish', 'agencies' => 'Molchanovs', 'licence' => 'works_with', 'works_with' => 'Saddle Line Freediving',
            'whatsapp' => '+201000000002', 'instagram' => 'https://instagram.com/', 'youtube' => 'https://youtube.com/',
            'base_area' => 'Blue Hole', 'spots' => 'blue-hole,lighthouse', 'pool' => 0, 'max_ratio' => 2, 'gear_included' => 0,
            'years_teaching' => 7, 'students_certified' => 600, 'personal_best' => 'CWT 92 m · FIM 85 m',
            'achievements' => "Molchanovs Instructor\nNational record holder (sample)\nDepth camp coach since 2021",
            'teaching_week' => 1, 'week_note' => 'Depth camp 3 spots open', 'verified' => 1, 'featured' => 1, 'founding' => 1,
            'services' => [
                ['course', 3, 'Molchanovs', 'Molchanovs Wave 3', 4, 450, 'Max 2 per coach'],
                ['coaching', 0, '', 'Private depth coaching', 0.5, 45, 'Video analysis included'],
                ['coaching', 0, '', '6-day depth camp', 6, 290, ''],
            ],
        ],
        [
            'slug' => 'blue-rim-freedivers', 'name' => 'Blue Rim Freedivers', 'type' => 'school',
            'tagline' => 'Our own pool, three agencies, every level from first breath to instructor.',
            'bio' => "A bigger school with its own training pool, so the first day is always relaxed. We teach SSI and AIDA, in five languages, and run instructor programs twice a year.",
            'languages' => 'English,Russian,German,Arabic,French', 'agencies' => 'SSI,AIDA', 'licence' => 'centre',
            'whatsapp' => '+201000000003', 'instagram' => 'https://instagram.com/', 'facebook' => 'https://facebook.com/', 'website' => 'https://example.com/',
            'base_area' => 'Lighthouse', 'spots' => 'lighthouse,blue-hole,three-pools', 'pool' => 1, 'max_ratio' => 4, 'gear_included' => 1,
            'years_teaching' => 14, 'students_certified' => 5200, 'personal_best' => '',
            'achievements' => "SSI Instructor Training Center\nOwn 20 m training pool\nCourses in 5 languages",
            'teaching_week' => 1, 'week_note' => 'Daily courses, any day start', 'verified' => 1, 'featured' => 0, 'founding' => 1,
            'services' => [
                ['course', 0, 'SSI', 'Try freediving (pool)', 0.5, 55, ''],
                ['course', 1, 'SSI', 'SSI Freediver Level 1', 3, 280, 'Pool day included'],
                ['course', 2, 'SSI', 'SSI Freediver Level 2', 3, 330, ''],
                ['course', 3, 'SSI', 'SSI Freediver Level 3', 4, 420, ''],
                ['course', 4, 'SSI', 'Instructor program', 21, 2200, 'Twice a year'],
                ['coaching', 0, '', 'Group line training', 0.5, 30, ''],
            ],
        ],
        [
            'slug' => 'reef-and-breath', 'name' => 'Reef & Breath Academy', 'type' => 'school',
            'tagline' => 'Friendly, affordable AIDA courses on Eel Garden beach.',
            'bio' => "A relaxed, budget-friendly school at the Eel Garden end of town. Good for travellers who want a solid AIDA certificate without the extras.",
            'languages' => 'English,Arabic', 'agencies' => 'AIDA', 'licence' => 'centre',
            'whatsapp' => '+201000000004', 'instagram' => 'https://instagram.com/',
            'base_area' => 'Eel Garden', 'spots' => 'eel-garden,lighthouse,mashraba', 'pool' => 0, 'max_ratio' => 4, 'gear_included' => 1,
            'years_teaching' => 5, 'students_certified' => 900, 'personal_best' => 'CWT 55 m',
            'achievements' => "AIDA Instructor\nBest price for AIDA 2 in town (sample)",
            'teaching_week' => 0, 'verified' => 0, 'featured' => 0, 'founding' => 1,
            'services' => [
                ['course', 1, 'AIDA', 'AIDA 2 Freediver', 2.5, 195, ''],
                ['course', 2, 'AIDA', 'AIDA 3 Advanced Freediver', 3, 245, ''],
                ['fun', 0, '', 'Guided fun freedive', 0.5, 25, 'Eel Garden or Islands'],
            ],
        ],
        [
            'slug' => 'omar-h-freelance', 'name' => 'Omar H.', 'type' => 'freelance',
            'tagline' => 'Born in Dahab. Private AIDA courses in Arabic or English.',
            'bio' => "I grew up on this coast and have been freediving since I was a kid. I teach private and small courses, and I know where the fish are on every reef from Abu Galum to the Islands.",
            'languages' => 'Arabic,English', 'agencies' => 'AIDA', 'licence' => 'works_with', 'works_with' => 'Reef & Breath Academy',
            'whatsapp' => '+201000000005', 'instagram' => 'https://instagram.com/', 'tiktok' => 'https://tiktok.com/',
            'base_area' => 'Mashraba', 'spots' => 'islands,abu-galum,eel-garden,blue-hole', 'pool' => 0, 'max_ratio' => 2, 'gear_included' => 1,
            'years_teaching' => 4, 'students_certified' => 300, 'personal_best' => 'CWT 60 m',
            'achievements' => "AIDA Instructor\nAbu Galum freedive trips",
            'teaching_week' => 1, 'week_note' => 'Free Wed–Sat', 'verified' => 1, 'featured' => 0, 'founding' => 0,
            'services' => [
                ['course', 1, 'AIDA', 'Private AIDA 2', 2.5, 280, '1–2 students'],
                ['guiding', 0, '', 'Abu Galum freedive day trip', 1, 60, 'Jeep and lunch included'],
                ['fun', 0, '', 'Reef freedive tour', 0.5, 30, ''],
            ],
        ],
        [
            'slug' => 'sand-dune-divers', 'name' => 'Sand Dune Divers', 'type' => 'centre',
            'tagline' => 'Scuba and freediving under one roof, with daily jeep trips north.',
            'bio' => "A PADI dive centre that also teaches freediving. Handy if one of you scuba dives and the other freedives: same jeep, same reef.",
            'languages' => 'English,German,Polish', 'agencies' => 'PADI,AIDA', 'licence' => 'centre',
            'whatsapp' => '+201000000006', 'website' => 'https://example.com/', 'facebook' => 'https://facebook.com/',
            'base_area' => 'Mashraba', 'spots' => 'blue-hole,bells,canyon,islands,gabr-el-bint', 'pool' => 1, 'max_ratio' => 4, 'gear_included' => 1,
            'years_teaching' => 18, 'students_certified' => 8000, 'personal_best' => '',
            'achievements' => "PADI 5 Star centre (sample)\nDaily jeep trips to the Blue Hole",
            'teaching_week' => 1, 'week_note' => 'Scuba daily · freediving Tue/Thu', 'verified' => 0, 'featured' => 0, 'founding' => 0,
            'services' => [
                ['course', 1, 'AIDA', 'AIDA 2 Freediver', 3, 240, ''],
                ['scuba', 1, 'PADI', 'PADI Open Water', 4, 340, ''],
                ['scuba', 0, 'PADI', 'Blue Hole & Bells 2 guided dives', 0.5, 60, 'Transport included'],
                ['rental', 0, '', 'Freediving kit', 1, 12, 'Per day'],
            ],
        ],
        [
            'slug' => 'coach-yuri-v', 'name' => 'Yuri V.', 'type' => 'coach',
            'tagline' => 'Equalisation clinic: fix the thing that stops you at 20 m.',
            'bio' => "Most freedivers stop because of equalisation, not breath. I run dry and in-water equalisation clinics and private depth coaching in Russian, Ukrainian and English.",
            'languages' => 'Russian,Ukrainian,English', 'agencies' => 'Molchanovs,AIDA', 'licence' => 'freelance',
            'whatsapp' => '+201000000007', 'instagram' => 'https://instagram.com/', 'youtube' => 'https://youtube.com/',
            'base_area' => 'Lighthouse', 'spots' => 'lighthouse,blue-hole', 'pool' => 0, 'max_ratio' => 3, 'gear_included' => 0,
            'years_teaching' => 6, 'students_certified' => 450, 'personal_best' => 'CWT 85 m',
            'achievements' => "Molchanovs Instructor\nEqualisation clinic author",
            'teaching_week' => 0, 'verified' => 0, 'featured' => 0, 'founding' => 0,
            'services' => [
                ['course', 2, 'Molchanovs', 'Molchanovs Wave 2', 3, 300, ''],
                ['coaching', 0, '', 'Equalisation clinic (dry + water)', 1, 70, ''],
                ['coaching', 0, '', 'Private depth coaching', 0.5, 40, ''],
            ],
        ],
        [
            'slug' => 'new-applicant-sample', 'name' => 'Deep Calm Freediving', 'type' => 'school', 'status' => 'pending',
            'tagline' => 'A new application waiting for approval.',
            'bio' => 'Sample application so you can try the admin approval flow.',
            'languages' => 'English,French', 'agencies' => 'AIDA', 'licence' => 'freelance',
            'whatsapp' => '+201000000008', 'base_area' => 'Lighthouse', 'spots' => 'lighthouse',
            'submitted_courses' => "AIDA 2 – 3 days – €230\nCoached session – €35",
            'services' => [],
        ],
    ];

    $ins = $pdo->prepare('INSERT INTO providers (slug,name,type,tagline,bio,languages,agencies,licence,works_with,whatsapp,email,website,instagram,tiktok,youtube,facebook,base_area,spots,pool,max_ratio,gear_included,years_teaching,students_certified,personal_best,achievements,teaching_week,week_note,week_updated,status,verified,featured,founding,is_sample,checked_on,submitted_courses)
        VALUES (:slug,:name,:type,:tagline,:bio,:languages,:agencies,:licence,:works_with,:whatsapp,:email,:website,:instagram,:tiktok,:youtube,:facebook,:base_area,:spots,:pool,:max_ratio,:gear_included,:years_teaching,:students_certified,:personal_best,:achievements,:teaching_week,:week_note,:week_updated,:status,:verified,:featured,:founding,1,:checked_on,:submitted_courses)');
    $svc = $pdo->prepare('INSERT INTO services (provider_id,category,level,agency,title,days,price_eur,notes) VALUES (?,?,?,?,?,?,?,?)');

    foreach ($providers as $p) {
        $row = [
            'slug' => $p['slug'], 'name' => $p['name'], 'type' => $p['type'], 'tagline' => $p['tagline'] ?? '', 'bio' => $p['bio'] ?? '',
            'languages' => $p['languages'] ?? '', 'agencies' => $p['agencies'] ?? '', 'licence' => $p['licence'] ?? 'freelance',
            'works_with' => $p['works_with'] ?? '', 'whatsapp' => $p['whatsapp'] ?? '', 'email' => $p['email'] ?? '',
            'website' => $p['website'] ?? '', 'instagram' => $p['instagram'] ?? '', 'tiktok' => $p['tiktok'] ?? '',
            'youtube' => $p['youtube'] ?? '', 'facebook' => $p['facebook'] ?? '', 'base_area' => $p['base_area'] ?? '',
            'spots' => $p['spots'] ?? '', 'pool' => $p['pool'] ?? 0, 'max_ratio' => $p['max_ratio'] ?? 0,
            'gear_included' => $p['gear_included'] ?? 0, 'years_teaching' => $p['years_teaching'] ?? 0,
            'students_certified' => $p['students_certified'] ?? 0, 'personal_best' => $p['personal_best'] ?? '',
            'achievements' => $p['achievements'] ?? '', 'teaching_week' => $p['teaching_week'] ?? 0,
            'week_note' => $p['week_note'] ?? '', 'week_updated' => !empty($p['teaching_week']) ? $today : '',
            'status' => $p['status'] ?? 'approved', 'verified' => $p['verified'] ?? 0, 'featured' => $p['featured'] ?? 0,
            'founding' => $p['founding'] ?? 0, 'checked_on' => ($p['status'] ?? 'approved') === 'approved' ? $today : '',
            'submitted_courses' => $p['submitted_courses'] ?? '',
        ];
        $ins->execute($row);
        $id = (int)$pdo->lastInsertId();
        foreach ($p['services'] as $s) $svc->execute([$id, $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6]]);
    }

    // Sample photos (Unsplash, free licence) so the prototype looks real. They go when the sample providers go.
    $media = [
        'saddle-line-freediving' => [['photo-1462947760324-15811216b688', 'bomanbo', 'Down the line', 'lighthouse'], ['photo-1602199926649-2e5e447bab97', 'kirildobrev', 'Reef session', 'blue-hole'], ['photo-1644387893399-25ece6338ab8', 'dapino', 'Breathe-up at the buoy', 'lighthouse']],
        'coach-lina-r' => [['photo-1502209524164-acea936639a2', 'jeremybishop', 'Into the deep', 'blue-hole'], ['photo-1628630500614-1c8924c99c3e', 'sandbarproductions', 'Freefall', 'blue-hole'], ['photo-1614241580814-477d221ebaeb', 'shathiu', 'Recovery breathing', '']],
        'blue-rim-freedivers' => [['photo-1544551763-dbfcd20d8e6c', 'sebaspenalambarri', 'First open-water session', 'three-pools'], ['photo-1503177847378-d2048487fa46', 'jeremybishop', 'Beginner day', 'lighthouse'], ['photo-1682687981674-0927add86f2b', 'neom', 'Coming up slowly', '']],
        'reef-and-breath' => [['photo-1778343215246-7b46a7cd30ae', 'mero_dnt', 'With the fish', 'eel-garden'], ['photo-1621432253242-09eece55193c', 'boardshortsben', 'Fun dive', 'eel-garden']],
        'omar-h-freelance' => [['photo-1682686581312-506a8b53190e', 'neom', 'Reef tour', 'islands'], ['photo-1603259860985-5dfe6bf9a1fd', 'israelgilfilms', 'Private session', 'abu-galum']],
        'sand-dune-divers' => [['photo-1758968571011-af0157c61d7b', 'nirhimi', 'Scuba at the Blue Hole', 'blue-hole'], ['photo-1567628065080-ecdec09a7588', 'maxgotts', 'Into the blue', 'bells']],
        'coach-yuri-v' => [['photo-1683009427666-340595e57e43', 'neom', 'Equalisation drills', 'lighthouse']],
    ];
    $mi = $pdo->prepare("INSERT INTO media (provider_id, file, kind, caption, spot, status, credit) VALUES (?, ?, 'photo', ?, ?, 'approved', ?)");

    $pid = fn($slug) => (int)$pdo->query('SELECT id FROM providers WHERE slug = ' . $pdo->quote($slug))->fetchColumn();
    foreach ($media as $slug => $items) foreach ($items as [$file, $credit, $cap, $spot]) $mi->execute([$pid($slug), $file, $cap, $spot, $credit]);

    $ev = $pdo->prepare('INSERT INTO events (provider_id,title,kind,starts_on,ends_on,description,is_sample) VALUES (?,?,?,?,?,?,1)');
    $d = fn($n) => date('Y-m-d', strtotime("+$n days"));
    $ev->execute([$pid('coach-lina-r'), 'Blue Hole depth camp', 'camp', $d(3), $d(8), 'Six days of coached depth training, max 2 athletes per coach.']);
    $ev->execute([$pid('blue-rim-freedivers'), 'Beginner week', 'course', $d(1), $d(5), 'SSI Level 1 with a pool day, then open water at Lighthouse.']);
    $ev->execute([$pid('coach-yuri-v'), 'Equalisation clinic', 'workshop', $d(5), '', 'Dry and in-water session on Frenzel and mouthfill.']);
    $ev->execute([null, 'Freedivers\' yin yoga', 'social', $d(2), '', 'Slow stretching for the diaphragm and ribs. All welcome.']);
    $ev->execute([$pid('omar-h-freelance'), 'Abu Galum freedive day', 'trip', $d(6), '', 'Jeep north, two reef sessions and a Bedouin lunch.']);
}
