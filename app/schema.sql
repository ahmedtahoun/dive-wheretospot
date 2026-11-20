CREATE TABLE IF NOT EXISTS providers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  type TEXT NOT NULL DEFAULT 'school',
  tagline TEXT DEFAULT '',
  bio TEXT DEFAULT '',
  languages TEXT DEFAULT '',
  agencies TEXT DEFAULT '',
  licence TEXT DEFAULT 'freelance',
  works_with TEXT DEFAULT '',
  whatsapp TEXT DEFAULT '',
  email TEXT DEFAULT '',
  website TEXT DEFAULT '',
  instagram TEXT DEFAULT '',
  tiktok TEXT DEFAULT '',
  youtube TEXT DEFAULT '',
  facebook TEXT DEFAULT '',
  base_area TEXT DEFAULT '',
  spots TEXT DEFAULT '',
  pool INTEGER DEFAULT 0,
  max_ratio INTEGER DEFAULT 0,
  gear_included INTEGER DEFAULT 0,
  years_teaching INTEGER DEFAULT 0,
  students_certified INTEGER DEFAULT 0,
  personal_best TEXT DEFAULT '',
  achievements TEXT DEFAULT '',
  teaching_week INTEGER DEFAULT 0,
  week_note TEXT DEFAULT '',
  week_updated TEXT DEFAULT '',
  status TEXT NOT NULL DEFAULT 'pending',
  verified INTEGER DEFAULT 0,
  verified_note TEXT DEFAULT '',
  featured INTEGER DEFAULT 0,
  founding INTEGER DEFAULT 0,
  is_sample INTEGER DEFAULT 0,
  checked_on TEXT DEFAULT '',
  submitted_courses TEXT DEFAULT '',
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS services (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  provider_id INTEGER NOT NULL REFERENCES providers(id) ON DELETE CASCADE,
  category TEXT NOT NULL DEFAULT 'course',
  level INTEGER DEFAULT 1,
  agency TEXT DEFAULT '',
  title TEXT NOT NULL,
  days REAL DEFAULT 0,
  price_eur REAL DEFAULT 0,
  notes TEXT DEFAULT ''
);

CREATE TABLE IF NOT EXISTS media (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  provider_id INTEGER NOT NULL REFERENCES providers(id) ON DELETE CASCADE,
  file TEXT NOT NULL,
  kind TEXT DEFAULT 'photo',
  caption TEXT DEFAULT '',
  spot TEXT DEFAULT '',
  status TEXT DEFAULT 'pending',
  credit TEXT DEFAULT '',
  created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  provider_id INTEGER REFERENCES providers(id) ON DELETE SET NULL,
  title TEXT NOT NULL,
  kind TEXT DEFAULT 'camp',
  starts_on TEXT NOT NULL,
  ends_on TEXT DEFAULT '',
  description TEXT DEFAULT '',
  link TEXT DEFAULT '',
  is_sample INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS inquiries (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ref TEXT NOT NULL,
  provider_id INTEGER REFERENCES providers(id) ON DELETE SET NULL,
  name TEXT NOT NULL,
  email TEXT DEFAULT '',
  whatsapp TEXT DEFAULT '',
  dates TEXT DEFAULT '',
  level TEXT DEFAULT '',
  people INTEGER DEFAULT 1,
  message TEXT DEFAULT '',
  status TEXT DEFAULT 'new',
  created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS clicks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  provider_id INTEGER REFERENCES providers(id) ON DELETE CASCADE,
  kind TEXT NOT NULL,
  page TEXT DEFAULT '',
  created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS hits (k TEXT NOT NULL, at INTEGER NOT NULL);
CREATE INDEX IF NOT EXISTS hits_k ON hits (k, at);
CREATE INDEX IF NOT EXISTS clicks_p ON clicks (provider_id, created_at);
