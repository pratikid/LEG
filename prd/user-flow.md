# User Flow

## Main User Journeys & Rationale

### 1. Onboarding & Account Management
- **Registration/Login:** User creates an account or logs in with secure credentials.
- **Profile Setup:** User sets up profile, preferences, and notification settings.
- **Edge Cases:** Password reset, email verification, and account deletion.
- **Value:** Lowers barrier to entry, personalizes experience, and supports secure collaboration.

### 2. Tree Creation & Management
- **Create New Tree:** User starts a new family tree (manual entry or GEDCOM import).
- **Add/Edit Individuals:** Add, edit, or remove individuals, partners, children, and parents.
- **Tree Visualization:** Visualize and interact with the tree (zoom, pan, switch layouts).
- **Edge Cases:** Duplicate detection, merge trees, undo/redo actions.
- **Value:** Empowers users to build and maintain accurate, rich family trees.

### 3. Individual & Group Management
- **View/Edit Individuals:** Access detailed profiles, timelines, and relationships.
- **Group Management:** Create and manage groups, assign members, and set permissions.
- **Edge Cases:** Orphaned individuals, group membership changes, privacy settings.
- **Value:** Supports collaboration, organization, and granular access control.

### 4. Media & Source Management
- **Upload Media:** Attach photos, documents, and media to individuals or events.
- **Source Citation:** Add and manage sources for facts and relationships.
- **Edge Cases:** File type/size validation, duplicate media, missing sources.
- **Value:** Enriches family stories and promotes research integrity.

### 5. Search & Navigation
- **Advanced Search:** Find individuals, groups, or events by name, date, or attribute.
- **Navigation:** Move between tree view, lists, and search results.
- **Edge Cases:** No results found, ambiguous queries, search within groups.
- **Value:** Increases usability and data discoverability.

### 6. Community & Collaboration
- **Join/Create Groups:** Users join or create groups for shared research.
- **Discussion & Forums:** Participate in group discussions (future feature).
- **Share Trees:** Invite collaborators or share view-only links.
- **Edge Cases:** Invitation management, group privacy, moderation.
- **Value:** Fosters community, knowledge sharing, and collaborative research.

### 7. Export & Reporting
- **Export Tree:** Download tree as image, PDF, or shareable link.
- **Printable Reports:** Generate and print detailed reports for individuals or families.
- **Edge Cases:** Large tree exports, custom report templates, export errors.
- **Value:** Supports offline sharing, documentation, and research output.

## User Journey Flow

```mermaid
flowchart TD
    A[User Visits LEG] --> B{Has Account?}
    B -->|No| C[Register Account]
    B -->|Yes| D[Login]
    C --> E[Profile Setup]
    D --> E
    E --> F[Choose Action]
    
    F --> G[Create New Tree]
    F --> H[Join Existing Tree]
    F --> I[Import GEDCOM]
    F --> J[Browse Community]
    
    G --> K[Add Individuals]
    H --> L[View Tree]
    I --> M[Parse & Import]
    J --> N[Find Groups]
    
    K --> O[Build Relationships]
    L --> P[Edit Tree]
    M --> Q[Review Import]
    N --> R[Join Group]
    
    O --> S[Add Media & Sources]
    P --> S
    Q --> T[Resolve Conflicts]
    R --> U[Collaborate]
    
    S --> V[Visualize Tree]
    T --> V
    U --> V
    V --> W[Export/Share]
    W --> X[Continue Research]
    X --> F
```

## Data Flow & Real-Time Updates

```mermaid
sequenceDiagram
    participant U as User
    participant LW as Livewire
    participant C as Controller
    participant M as Model
    participant PG as PostgreSQL
    participant N4J as Neo4j
    participant R as Redis
    
    U->>LW: User Action
    LW->>C: AJAX Request
    C->>M: Business Logic
    M->>PG: Data Query/Update
    M->>N4J: Relationship Query/Update
    C->>R: Cache Check/Update
    C->>LW: Response
    LW->>U: Real-time UI Update
    
    Note over U,R: All updates are immediate and collaborative
```

## How Flows Support Platform Goals
- **Engagement:** Intuitive flows and real-time feedback keep users active and invested.
- **Collaboration:** Group and sharing flows enable collective research and community building.
- **Data Integrity:** Validation, source management, and activity logs ensure high-quality, trustworthy data.
- **Extensibility:** Modular flows allow for easy addition of new features and user journeys as the platform evolves. 