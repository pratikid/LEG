# Project Timeline

## Phased Milestones & Progress

### Phase 1: Foundation & MVP
- [x] User account system (enables secure access, personalization)
- [x] Family tree CRUD (create, read, update, delete) (core data management)
- [x] Individual and group management (collaboration, organization)
- [x] Interactive tree visualization (D3.js) (core user value, engagement)
- [x] GEDCOM import UI (file upload); [ ] Parsing/export logic; [ ] Source/note linking (Future)

### Phase 2: Collaboration & Media
- [ ] Source citation management (research integrity) **(GEDCOM source/note linking: Future)**
- [x] Media uploads and gallery (basic UI); [ ] Advanced features
- [x] Timeline/event management (basic CRUD); [ ] Advanced event types, reporting
- [x] Search and filtering (basic); [ ] Advanced/relationship/event search
- [x] Activity logs and notifications (basic); [ ] Advanced
- [x] Community/group features (basic); [ ] Forums, advanced collaboration

### Phase 3: Privacy, Accessibility, & Advanced Features
- [ ] Privacy controls (user trust, compliance)
- [ ] Accessibility and i18n (inclusivity, global reach)
- [ ] Advanced search/filtering (power users, data mining)
- [ ] Drag-and-drop tree editing (usability, engagement)
- [ ] Geographic mapping (visual insights)
- [ ] Event planning & RSVP (community engagement)

## Controller Implementation TODOs

### TreeController
- [x] index: List all trees
- [x] create: Show form to create a tree
- [x] store: Store a new tree
- [x] show: Display a single tree
- [x] edit: Show form to edit a tree
- [x] update: Update a tree
- [x] destroy: Delete a tree
- [x] handleImport: Import GEDCOM file (UI only)
- [ ] Enhance validation, error handling, and user feedback for all actions
- [ ] Implement tree sharing, export, and advanced search (future)
- [ ] **Handle GEDCOM sources/notes in import/export (Future)**

### IndividualController
- [x] index: List all individuals
- [x] create: Show form to create an individual (with tree selection)
- [x] store: Store a new individual
- [x] show: Display an individual (with relationship management)
- [x] edit: Show form to edit an individual
- [x] update: Update an individual
- [x] destroy: Delete an individual
- [ ] Add bulk import/export (GEDCOM) for individuals (future)
- [ ] Implement advanced search/filtering and reporting (future)

### GroupController
- [x] index: List all groups
- [x] create: Show form to create a group (with tree selection)
- [x] store: Store a new group
- [x] show: Display a group
- [x] edit: Show form to edit a group
- [x] update: Update a group
- [x] destroy: Delete a group
- [ ] Implement group collaboration features (discussions, invitations, permissions)
- [ ] Add group activity logs and notifications (future)

### TimelineEventController
- [x] index: List all timeline events
- [x] create: Show form to create a timeline event
- [x] store: Store a new timeline event
- [x] show: Display a timeline event
- [x] edit: Show form to edit a timeline event
- [x] update: Update a timeline event
- [x] destroy: Delete a timeline event
- [ ] Implement event types, filtering, and advanced reporting
- [ ] Add event import/export and calendar integration (future)

### SourceController, StoryController, MediaController
- [x] index: List all items
- [x] create: Show form to create an item
- [x] show: Display an item
- [x] edit: Show form to edit an item
- [ ] store: Not implemented (placeholder)
- [ ] update: Not implemented (placeholder)
- [ ] destroy: Not implemented (placeholder)
- [ ] Implement full CRUD for sources, stories, and media
- [ ] Add media upload, association, and advanced search (future)
- [ ] Enable source citation linking and management
- [ ] **Handle GEDCOM sources/notes in import/export (Future)**

### Neo4jRelationshipController
- [x] All core relationship management (parent, child, spouse, sibling)
- [x] Advanced queries (ancestors, descendants, siblings, shortest path)
- [x] Add/remove relationships via AJAX and forms
- [ ] Add relationship type management, bulk operations, and advanced graph analytics (future)

### CommunityController, ToolsController, HelpController, AdminController
- [x] Basic views and navigation
- [ ] Implement community forums, admin notifications, and advanced tools
- [ ] Add user guides, tutorials, and support features

## Timeline & Delivery

- **Phase 1 (MVP):** 1-2 months (core features, onboarding, initial user value)
- **Phase 2:** 2-3 months (collaboration, media, and reporting)
- **Phase 3:** 3-4 months (privacy, accessibility, advanced features)

## Notes
- Update this file as features are completed or requirements change.
- Each milestone is designed to deliver incremental value, supporting user growth, engagement, and long-term ROI.
- **Note:** Handling of sources and notes in GEDCOM import/export is planned for a future release. 