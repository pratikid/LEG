# Features

## Key Features

* **Curated List of Tools & Software:** A categorized compilation of genealogy software (both free and paid), online platforms, and mobile applications to help users build and manage their family trees.
* **Educational Materials:** Links to articles, tutorials, guides, and online courses covering various aspects of genealogical research, from basic principles to advanced techniques.
* **Community Forum (Future):** A dedicated space for users to ask questions, share discoveries, discuss challenges, and connect with fellow genealogy enthusiasts. (This may be hosted externally or integrated later).
* **Links to Data Sources & Archives:** A categorized directory of online and offline resources such as census records, vital records, immigration documents, historical societies, and genealogical databases worldwide.
* **Templates & Guides:** Downloadable templates for creating family tree charts, research logs, and other useful documents, along with guides on how to effectively use them.
* **Best Practices Documentation:** Guidelines and recommendations on ethical research, source citation, data privacy, and collaboration in genealogy.
* **Open Source Contributions (Future):** Opportunities for developers and enthusiasts to contribute to the potential development of open-source genealogical tools.

## Detailed Feature List

The LEG project aims to implement a comprehensive set of features, organized by complexity level:

### Lower Complexity Features
* [ ] User Account Creation
* [ ] Different User Roles
* [ ] Comprehensive User Guide
* [ ] Timeline View: Displaying a chronological list of events for an individual or family
* [ ] Printable Charts and Reports (Basic): Generating simple PDF outputs of the current tree view or basic reports
* [ ] Interactive Tutorials and Tooltips: Guiding new users with on-screen help
* [ ] Advanced Search and Filtering (Basic): Allowing users to search by name, birth date, etc., and filter the tree view
* [ ] Customizable Node Appearance (Basic): Allowing users to change node colors or choose basic display options
* [ ] User Activity Logs (Admin-focused): Tracking basic user actions within family groups
* [ ] Guest Access (View-only): Allowing logged-in users to share a view-only link to their tree
* [ ] Discussion Forums within Groups (Basic): Implementing a simple forum structure within each family group
* [ ] Shared Media Library (Basic): Allowing file uploads and association with individuals (basic organization)
* [ ] Notification Bar for Admins

### Medium Complexity Features
* [ ] Source Citation and Management (Basic): Allowing users to add text-based citations to facts
* [ ] Visually Pleasing Family Tree (Basic): Creating a basic, interactive family tree using nodes and relationships
* [ ] Multiple Tree Styles: Implementing different visual layouts like fan charts or descendant charts
* [ ] Customizable Node Appearance (Advanced): Offering more granular control over node elements and information displayed
* [ ] Relationship Visualizations: Highlighting specific relationship paths within the tree
* [ ] Bulk Import/Export (GEDCOM Support): Parsing and generating GEDCOM files
* [ ] Advanced Search and Filtering (Complex): Allowing searches based on relationships, events, and other criteria
* [ ] Storytelling Features (Text-based): Allowing users to write and associate stories with individuals
* [ ] Event Planning & RSVP: Implementing a system for creating and managing family events with RSVP functionality
* [ ] Intuitive Drag-and-Drop Interface: For visually editing the family tree
* [ ] Geographic Mapping (Basic): Displaying event locations on a map using basic markers
* [ ] Option to create community/groups for the family tree

### Higher Complexity Features
* [ ] Detailed Node Information:
  * [ ] Name
  * [ ] Date of Birth
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

## TODO

### TreeController
- **handleImport**: Implement actual GEDCOM parsing and import logic to process uploaded GEDCOM files and import tree data.
- **store**: Implement validation and storage logic for creating a new tree.
- **show**: Fetch tree by ID and display its details.
- **edit**: Fetch tree by ID for editing.
- **update**: Implement update logic for modifying an existing tree.
- **destroy**: Implement delete logic for removing a tree.

### IndividualController
- **index**: Fetch and display a list of individuals.
- **store**: Implement validation and storage logic for creating a new individual.
- **show**: Fetch individual by ID and display their details.
- **edit**: Fetch individual by ID for editing.
- **update**: Implement update logic for modifying an existing individual.
- **destroy**: Implement delete logic for removing an individual.

### GroupController
- **index**: Fetch and display a list of groups.
- **store**: Implement validation and storage logic for creating a new group.
- **show**: Fetch group by ID and display its details.
- **edit**: Fetch group by ID for editing.
- **update**: Implement update logic for modifying an existing group.
- **destroy**: Implement delete logic for removing a group. 