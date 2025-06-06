# Features

> **GEDCOM 5.5.5 Compliance:** See [GEDCOM_COMPLIANCE.md](./GEDCOM_COMPLIANCE.md) for a detailed compliance checklist and status. Sources/notes import/export is planned for future implementation.

## Key Features

* **Curated List of Tools & Software:** [x] Implemented (static list, needs dynamic/admin editing)
* **Educational Materials:** [x] Implemented (static links, needs admin editing)
* **Community Forum (Future):** [ ] Planned
* **Links to Data Sources & Archives:** [x] Implemented (static list, needs admin editing)
* **Templates & Guides:** [x] Implemented (static, needs admin editing)
* **Best Practices Documentation:** [x] Implemented
* **Open Source Contributions (Future):** [ ] Planned

## Detailed Feature List

The LEG project aims to implement a comprehensive set of features, organized by complexity level:

### Lower Complexity Features
* [x] User Account Creation
* [x] Different User Roles
* [x] Comprehensive User Guide
* [x] Timeline View: [x] Basic, [ ] Advanced/Filtering
* [x] Printable Charts and Reports (Basic): [x] PDF export, [ ] Custom templates
* [x] Interactive Tutorials and Tooltips: [x] Basic, [ ] Advanced
* [x] Advanced Search and Filtering (Basic): [x] Name/date, [ ] Relationship/event
* [x] Customizable Node Appearance (Basic): [x] Color, [ ] Advanced
* [x] User Activity Logs (Admin-focused): [x] Basic, [ ] Advanced
* [x] Guest Access (View-only): [x] Basic, [ ] Link sharing

### Medium Complexity Features
* [x] Visually Pleasing Family Tree (Basic): [x] D3.js, [ ] Advanced layouts
* [x] Multiple Tree Styles: [x] Pedigree, [ ] Fan/Descendant
* [x] Customizable Node Appearance (Advanced): [ ]
* [x] Relationship Visualizations: [x] Highlight direct line, [ ] Advanced paths
* [x] Bulk Import/Export (GEDCOM Support): [x] Import UI, [x] Parsing/Export
* [x] Advanced Search and Filtering (Complex): [ ]
* [x] Storytelling Features (Text-based): [x] Basic, [ ] Multimedia
* [x] Event Planning & RSVP: [ ]
* [x] Intuitive Drag-and-Drop Interface: [ ]
* [x] Geographic Mapping (Basic): [ ]
* [x] Option to create community/groups for the family tree: [x] Basic, [ ] Advanced

### Higher Complexity Features
* [ ] Detailed Node Information:
  * [x] Name
  * [x] Date of Birth
  * [ ] Profile Picture
  * [ ] Link to Social Profile
  * [ ] Achievements (listed date-wise)
* [ ] Customizable Layout of the Family Tree
* [ ] Fiction Character Example (Harry Potter)
* [ ] Record Hints/Suggestions: Integrating with external databases to suggest potential matches
* [ ] DNA Integration: Allowing users to link DNA results to their tree
* [ ] Research Collaboration Tools (Advanced): Implementing features like shared research logs and task assignment
* [ ] Privacy Controls (Granular): Implementing detailed privacy settings
* [ ] Shared Media Library (Advanced): Implementing features like tagging, searching within media, and version control
* [ ] Storytelling Features (Multimedia): Allowing the inclusion of photos, audio, and video in stories
* [ ] Printable Charts and Reports (Advanced): Generating highly customizable and visually rich reports
* [ ] Geographic Mapping (Advanced): Showing migration patterns and detailed historical maps
* [ ] Scalability and Performance Optimization (Ongoing)
* [ ] Accessibility (Comprehensive)
* [ ] Internationalization (i18n) and Localization (l10n)

### Future scope
* [ ] Source Citation and Management (Basic): Allowing users to add text-based citations to facts
* [ ] Discussion Forums within Groups (Basic): Implementing a simple forum structure within each family group
* [ ] Shared Media Library (Basic): Allowing file uploads and association with individuals (basic organization)
* [ ] Notification Bar for Admins


## TODO

### TreeController
- [x] handleImport: UI and file upload done, [x] GEDCOM parsing logic
- [x] store: Implemented
- [x] show: Implemented
- [x] edit: Implemented
- [x] update: Implemented
- [x] destroy: Implemented
- [ ] Enhance validation, error handling, user feedback
- [ ] Tree sharing, export, advanced search (future)

### IndividualController
- [x] index: Implemented
- [x] store: Implemented
- [x] show: Implemented
- [x] edit: Implemented
- [x] update: Implemented
- [x] destroy: Implemented
- [ ] Bulk import/export (GEDCOM), advanced search/filtering (future)

### GroupController
- [x] index: Implemented
- [x] store: Implemented
- [x] show: Implemented
- [x] edit: Implemented
- [x] update: Implemented
- [x] destroy: Implemented
- [ ] Collaboration features, activity logs (future) 