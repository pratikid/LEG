# User Flow

## Main User Journeys

### 1. Onboarding & Account Management
- User registers or logs in
- Sets up profile and preferences

### 2. Tree Creation & Management
- Create new family tree (manual or GEDCOM import)
- Add individuals, partners, children, parents
- Edit or delete nodes
- Visualize and interact with tree (zoom, pan, layout switch)

### 3. Individual & Group Management
- View, add, edit, or remove individuals
- Manage groups and assign members
- View individual profiles (timeline, facts, sources, media)

### 4. Media & Source Management
- Upload and associate media
- Add and manage source citations

### 5. Search & Navigation
- Use advanced search and filters
- Navigate via tree view, lists, or search results

### 6. Community & Collaboration
- Join or create groups
- Participate in discussions (future)
- Share trees or invite collaborators

### 7. Export & Reporting
- Export tree as image, PDF, or shareable link
- Print or download reports

## Data Flow
- All user actions update the database (Eloquent/Neo4j)
- Real-time UI updates via Livewire
- Visualizations rendered with D3.js 