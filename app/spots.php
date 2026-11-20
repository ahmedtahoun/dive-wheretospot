<?php
// Dahab spots. Facts are general public knowledge, kept cautious ("about", "reported").
// Coordinates are approximate pins. Exposure (0 sheltered, 1 some, 2 exposed) is to the usual north wind:
// a first estimate, to be checked with local instructors before launch.
//
// profile.rock: polygons of [x 0..100, depth in metres] drawn as the cross-section on the spot page.
// profile.features: [depth, kind, title, text]  kind = info | life | warn | course
return [
  'blue-hole' => [
    'name' => 'Blue Hole', 'area' => 'North', 'lat' => 28.5721, 'lon' => 34.5370,
    'freedive' => true, 'scuba' => true, 'exposure' => 1,
    'depth' => '100–130 m (reported)', 'level' => 'All levels on the line · Arch: technical only',
    'entry' => 'Shore, about 10 km north of town. A gate fee is usually charged.',
    'summary' => 'The world-famous sinkhole: deep, calm water a few fin kicks from the shore. Dahab\'s home of deep freediving.',
    'about' => [
      'The Blue Hole is a submarine sinkhole on the coast north of Dahab, reported at 100–130 m deep. The water inside the rim is usually calm, with little current, which is why freedivers come here from all over the world to train on a line with a safety team.',
      'On the seaward side the rim has a shallow opening at about 6 m called the Saddle. Much deeper, at roughly 52–55 m, a tunnel known as the Arch runs through the wall to the open sea. The Arch is for technical divers only: it has cost many lives, and memorial plaques line the shore.',
    ],
    'best_for' => ['Depth training on a line', 'Beginner open-water sessions', 'Scuba: Bells to Blue Hole drift'],
    'safety' => ['Never freedive alone: one up, one down, always.', 'The Arch is technical-only. Do not attempt it on a breath hold or recreational gear.', 'Train inside your course limits with a qualified buddy or coach.'],
    'profile' => [
      'max' => 110,
      'rock' => [
        [[0,-4],[9,-4],[9,0],[11,2],[13,28],[14,62],[16,96],[20,108],[36,110],[43,96],[46,86],[54,90],[72,104],[100,118],[100,125],[0,125]],
        [[42,6],[49,6],[51,18],[53,36],[54,52],[46,52],[44,32]],
      ],
      'features' => [
        [0, 'info', 'Surface', 'Walk in from the shore and swim out to the buoy. Lines are dropped in the middle of the hole.'],
        [4, 'life', 'Rim reef', 'Anthias, butterflyfish and soft corals on the wall of the rim.'],
        [6, 'info', 'The Saddle', 'The shallow opening to the sea at about 6 m. Scuba divers finish the Bells drift through here.'],
        [12, 'life', 'Blue water', 'A Napoleon wrasse sometimes cruises by. Below this the wall fades into blue.'],
        [55, 'warn', 'The Arch (~52–55 m)', 'A tunnel through the wall to the open sea, about 26 m long. Technical diving only. Many divers have died here.'],
        [70, 'info', 'Deep training', 'Competitive freedivers train past here on a line, with a full safety team.'],
        [100, 'info', 'The deep', 'The bottom is reported at 100–130 m. Few will ever see it.'],
      ],
    ],
  ],
  'lighthouse' => [
    'name' => 'Lighthouse', 'area' => 'Town', 'lat' => 28.4998, 'lon' => 34.5182,
    'freedive' => true, 'scuba' => true, 'exposure' => 0,
    'depth' => '40 m+', 'level' => 'Beginner to advanced',
    'entry' => 'Shore, in town. Steps and easy entry next to the schools.',
    'summary' => 'Dahab\'s everyday training bay: sheltered, in town, with lines set a short swim from the steps.',
    'about' => [
      'Lighthouse is the bay at the north end of Dahab\'s seafront, named after the small lighthouse on the point. Most freediving schools are a short walk away and run their daily line sessions here.',
      'The reef slopes away quickly from the entry, so beginners and deep divers can train in the same bay. It is usually one of the calmer spots when the north wind blows.',
    ],
    'best_for' => ['Daily line training', 'Beginner courses', 'A quick session before work'],
    'safety' => ['Watch for boats and divers entering from the shore.', 'Always train with a buddy on the line.'],
    'profile' => [
      'max' => 40,
      'rock' => [[[0,-4],[8,-4],[8,0],[12,2],[20,6],[30,12],[45,22],[62,32],[80,40],[100,46],[100,50],[0,50]]],
      'features' => [
        [0, 'info', 'Entry', 'Steps into the water right in front of the schools. Buoys and lines are a short swim out.'],
        [5, 'life', 'Reef slope', 'Coral heads, lionfish and reef fish along the slope.'],
        [15, 'info', 'Line sessions', 'The typical depth for first course dives on the line.'],
        [35, 'info', 'Sand and blue', 'Deeper lines are set further out for advanced training.'],
      ],
    ],
  ],
  'canyon' => [
    'name' => 'The Canyon', 'area' => 'North', 'lat' => 28.5530, 'lon' => 34.5300,
    'freedive' => true, 'scuba' => true, 'exposure' => 1,
    'depth' => '18–50 m', 'level' => 'Intermediate and up',
    'entry' => 'Shore, about 5 km north of town, across a shallow reef.',
    'summary' => 'A dramatic crack in the reef with shafts of light, loved by scuba divers and photographers.',
    'about' => [
      'The Canyon is a long fissure in the reef north of town. The canyon floor sits at roughly 18–30 m and opens deeper at its far end, with light falling through the gaps above.',
      'Freedivers visit for scenic dives over the reef and the canyon mouth. Swimming into overhead sections on a breath hold needs specific training and is not for beginners.',
    ],
    'best_for' => ['Scenic dives', 'Underwater photos', 'Scuba: swim-throughs'],
    'safety' => ['No overhead swim-throughs on a breath hold without specific training.', 'Surge at the entry on windy days.'],
    'profile' => [
      'max' => 50,
      'rock' => [[[0,-4],[8,-4],[8,0],[14,3],[30,8],[38,16],[40,30],[44,34],[48,18],[56,20],[70,30],[88,44],[100,52],[100,56],[0,56]]],
      'features' => [
        [0, 'info', 'Reef flat', 'Swim across the shallow reef to the canyon entrance.'],
        [8, 'life', 'Glassfish', 'Clouds of glassfish gather at the canyon mouth.'],
        [18, 'info', 'Canyon entrance', 'The crack opens up. Light shafts fall from above.'],
        [30, 'warn', 'Canyon floor', 'Overhead sections. Scuba only unless you are trained for it.'],
        [45, 'info', 'Deep exit', 'The far end opens to the blue at about 45–50 m.'],
      ],
    ],
  ],
  'bells' => [
    'name' => 'The Bells', 'area' => 'North', 'lat' => 28.5752, 'lon' => 34.5377,
    'freedive' => false, 'scuba' => true, 'exposure' => 1,
    'depth' => '7–30 m+', 'level' => 'Advanced Open Water recommended',
    'entry' => 'Shore, a short walk north of the Blue Hole.',
    'summary' => 'A narrow chimney from 7 m down to the outer wall, then a drift south to the Blue Hole.',
    'about' => [
      'The Bells is one of Dahab\'s classic scuba dives: drop into a narrow chimney near the shore, exit onto a sheer wall, then drift south along it and finish over the Saddle of the Blue Hole.',
    ],
    'best_for' => ['Scuba wall drift', 'Combining with the Blue Hole'],
    'safety' => ['Stay within your certified depth on the wall.'],
    'profile' => [
      'max' => 40,
      'rock' => [[[0,-4],[10,-4],[10,0],[12,7],[13,30],[15,60],[18,80],[100,84],[100,90],[0,90]]],
      'features' => [
        [0, 'info', 'Entry', 'Step in from the rocks near the café huts.'],
        [7, 'info', 'The chimney', 'Drop through the narrow vertical chimney.'],
        [27, 'life', 'The wall', 'Exit onto the sheer outer wall full of soft corals.'],
        [30, 'warn', 'Watch your depth', 'The wall keeps going. Stay within your limits.'],
      ],
    ],
  ],
  'eel-garden' => [
    'name' => 'Eel Garden', 'area' => 'Town', 'lat' => 28.5063, 'lon' => 34.5188,
    'freedive' => true, 'scuba' => true, 'exposure' => 2,
    'depth' => '8–25 m', 'level' => 'All levels',
    'entry' => 'Shore, at the north end of town.',
    'summary' => 'A sandy slope full of garden eels swaying out of the sand. Easy, pretty and close to town.',
    'about' => ['At the north end of Dahab, a sandy slope at roughly 8–15 m is home to hundreds of garden eels. A relaxed spot for fun freedives, snorkelling and easy scuba.'],
    'best_for' => ['Fun freedives', 'Snorkelling', 'Easy scuba'],
    'safety' => ['More exposed to the north wind: check conditions first.'],
    'profile' => [
      'max' => 25,
      'rock' => [[[0,-4],[8,-4],[8,0],[18,3],[30,6],[50,10],[70,15],[90,21],[100,25],[100,30],[0,30]]],
      'features' => [
        [0, 'info', 'Entry', 'Walk in over the shallow reef at the end of the promenade.'],
        [8, 'life', 'Garden eels', 'Hundreds of eels poke out of the sand and vanish as you come close.'],
        [15, 'life', 'Coral heads', 'Scattered coral blocks with octopus and morays.'],
      ],
    ],
  ],
  'islands' => [
    'name' => 'The Islands', 'area' => 'South', 'lat' => 28.4703, 'lon' => 34.5102,
    'freedive' => true, 'scuba' => true, 'exposure' => 1,
    'depth' => '6–18 m', 'level' => 'All levels',
    'entry' => 'Shore, south of town.',
    'summary' => 'A maze of coral towers and sandy alleys: one of the prettiest reefs in Dahab.',
    'about' => ['A labyrinth of coral pinnacles in shallow water south of town, full of colour and fish. Easy to get lost in, so dive it with someone who knows the way.'],
    'best_for' => ['Coral and fish', 'Fun freedives', 'Scuba navigation'],
    'safety' => ['Easy to lose your way: go with a guide the first time.'],
    'profile' => [
      'max' => 20,
      'rock' => [[[0,-4],[8,-4],[8,0],[14,4],[18,2],[22,9],[28,3],[34,12],[40,4],[48,14],[56,6],[64,16],[74,10],[86,18],[100,20],[100,24],[0,24]]],
      'features' => [
        [0, 'info', 'Entry', 'Shallow entry over the reef.'],
        [6, 'life', 'Coral towers', 'Pinnacles of hard coral with anthias and butterflyfish.'],
        [14, 'life', 'Sandy alleys', 'Stingrays rest on the sand between the towers.'],
      ],
    ],
  ],
  'three-pools' => [
    'name' => 'Three Pools', 'area' => 'South', 'lat' => 28.4380, 'lon' => 34.4700,
    'freedive' => true, 'scuba' => true, 'exposure' => 0,
    'depth' => '3–15 m', 'level' => 'Beginners and snorkellers',
    'entry' => 'Shore, south of town.',
    'summary' => 'Three shallow pools linked by sandy channels: calm, clear and very beginner friendly.',
    'about' => ['Three sheltered pools joined by sandy channels. A favourite for first freedives, snorkelling and relaxed scuba when the wind is up elsewhere.'],
    'best_for' => ['First freedives', 'Snorkelling', 'Windy days'],
    'safety' => ['Shallow does not mean safe: still dive with a buddy.'],
    'profile' => [
      'max' => 15,
      'rock' => [[[0,-4],[8,-4],[8,0],[14,4],[24,6],[30,3],[38,8],[48,4],[58,10],[70,8],[84,13],[100,15],[100,18],[0,18]]],
      'features' => [
        [0, 'info', 'Entry', 'Walk in across the sand.'],
        [4, 'life', 'The pools', 'Calm, clear pools with small reef fish.'],
        [10, 'info', 'Practice depth', 'Good for first duck dives and relaxed breath-holds.'],
      ],
    ],
  ],
  'abu-helal' => [
    'name' => 'Abu Helal', 'area' => 'North', 'lat' => 28.5620, 'lon' => 34.5330,
    'freedive' => true, 'scuba' => true, 'exposure' => 2,
    'depth' => '30 m+', 'level' => 'Intermediate and up',
    'entry' => 'Shore, between the Canyon and the Blue Hole.',
    'summary' => 'A quieter wall north of town: a good alternative when the Blue Hole is busy.',
    'about' => ['A reef wall with fewer crowds than the Blue Hole, used for both scuba and freediving training on calm days.'],
    'best_for' => ['Quieter training', 'Wall dives'],
    'safety' => ['Exposed to the north wind. Check conditions first.'],
    'profile' => [
      'max' => 35,
      'rock' => [[[0,-4],[8,-4],[8,0],[12,2],[16,10],[20,24],[26,40],[40,52],[100,60],[100,64],[0,64]]],
      'features' => [
        [0, 'info', 'Entry', 'Shore entry over a short reef flat.'],
        [10, 'life', 'Reef wall', 'Hard corals and reef fish along the wall.'],
        [30, 'info', 'Blue', 'The wall keeps dropping into the blue.'],
      ],
    ],
  ],
  'mashraba' => [
    'name' => 'Mashraba', 'area' => 'Town', 'lat' => 28.4905, 'lon' => 34.5145,
    'freedive' => true, 'scuba' => true, 'exposure' => 1,
    'depth' => '5–20 m', 'level' => 'All levels',
    'entry' => 'Shore, right on the central seafront.',
    'summary' => 'The in-town house reef: quick, easy and right in front of the cafés.',
    'about' => ['The reef in front of the Mashraba seafront. Handy for a quick session, a check dive or snorkelling between meals.'],
    'best_for' => ['Quick sessions', 'Check dives', 'Snorkelling'],
    'safety' => ['Watch for boats near the shore.'],
    'profile' => [
      'max' => 20,
      'rock' => [[[0,-4],[8,-4],[8,0],[14,3],[26,6],[40,10],[60,14],[80,18],[100,21],[100,24],[0,24]]],
      'features' => [
        [0, 'info', 'Entry', 'Straight in from the seafront.'],
        [6, 'life', 'House reef', 'Coral heads, pufferfish and lionfish.'],
      ],
    ],
  ],
  'abu-galum' => [
    'name' => 'Ras Abu Galum', 'area' => 'North (protectorate)', 'lat' => 28.6200, 'lon' => 34.5600,
    'freedive' => true, 'scuba' => true, 'exposure' => 2,
    'depth' => '30 m+', 'level' => 'Intermediate and up',
    'entry' => 'Boat, jeep or camel from the Blue Hole. No road.',
    'summary' => 'A protected, roadless coast north of the Blue Hole with some of the cleanest reef in Sinai.',
    'about' => ['Ras Abu Galum is a protected area reached by boat, jeep or camel. Fewer divers, pristine walls and a Bedouin camp at the beach. A full-day trip.'],
    'best_for' => ['Day trips', 'Pristine reef'],
    'safety' => ['Remote: go with an operator who brings oxygen and a radio.'],
    'profile' => [
      'max' => 35,
      'rock' => [[[0,-4],[8,-4],[8,0],[12,3],[15,14],[18,30],[24,46],[100,56],[100,60],[0,60]]],
      'features' => [
        [0, 'info', 'Beach', 'Arrive by boat, jeep or camel.'],
        [8, 'life', 'Pristine wall', 'Healthy hard corals and big schools of fish.'],
        [30, 'info', 'Outer wall', 'Deeper wall for experienced divers.'],
      ],
    ],
  ],
  'gabr-el-bint' => [
    'name' => 'Gabr el Bint', 'area' => 'South (boat)', 'lat' => 28.3300, 'lon' => 34.4300,
    'freedive' => false, 'scuba' => true, 'exposure' => 1,
    'depth' => '30 m+', 'level' => 'Advanced Open Water',
    'entry' => 'Boat, about 40 minutes south.',
    'summary' => 'A remote southern wall reached by boat, with big coral and fewer divers.',
    'about' => ['A remote wall dive south of Dahab, usually done as a boat day trip.'],
    'best_for' => ['Scuba day trips', 'Wall dives'],
    'safety' => ['Remote: confirm your operator carries oxygen.'],
    'profile' => [
      'max' => 35,
      'rock' => [[[0,-4],[8,-4],[8,0],[12,4],[16,18],[20,34],[28,48],[100,58],[100,62],[0,62]]],
      'features' => [
        [0, 'info', 'Boat entry', 'Giant stride off the boat.'],
        [12, 'life', 'Coral wall', 'Big table corals and gorgonians.'],
        [30, 'info', 'Deeper wall', 'For experienced scuba divers.'],
      ],
    ],
  ],
];
