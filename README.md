# LEG - Lineage Exploration and Genealogy

[![Awesome](https://cdn.rawgit.com/sindresorhus/awesome/d7305f38d2aca4cf4c6f32d313e95770f38dd59d/media/badge.svg)](https://github.com/sindresorhus/awesome)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![GitHub Issues](https://img.shields.io/github/issues/pratikid/LEG.svg)](https://github.com/pratikid/LEG/issues)
[![GitHub Pull Requests](https://img.shields.io/github/pulls/pratikid/LEG.svg)](https://github.com/pratikid/LEG/pulls)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://opensource.org/)

**LEG** is a project dedicated to **Lineage Exploration and Genealogy**. It aims to provide resources, tools, and information for individuals interested in discovering, documenting, and understanding their family history. Whether you're a seasoned genealogist or just starting your ancestral journey, LEG offers a community and a collection of valuable materials to aid your exploration.

## Table of Contents

* [Introduction](#introduction)
* [Project Goals](#project-goals)
* [Key Features](#key-features)
* [Detailed Feature List](#detailed-feature-list)
* [User Interface & Experience Design](#user-interface--experience-design)
* [Getting Started](#getting-started)
    * [Contributing](#contributing)
    * [Code of Conduct](#code-of-conduct)
* [Resources](#resources)
    * [Tools & Software](#tools--software)
    * [Educational Materials](#educational-materials)
    * [Community Forum](#community-forum)
    * [Data Sources & Archives](#data-sources--archives)
* [Roadmap](#roadmap)
* [Support](#support)
* [License](#license)
* [Acknowledgements](#acknowledgements)

## Introduction

The desire to understand our roots is a fundamental human instinct. **LEG** (Lineage Exploration and Genealogy) is born from this desire, aiming to make the process of discovering and documenting family history more accessible, engaging, and collaborative. This project serves as a central hub for anyone interested in genealogy, offering a curated collection of resources, fostering a supportive community, and potentially developing open-source tools to aid in genealogical research.

We believe that everyone has a story to tell, and understanding our family's past enriches our present and shapes our future. LEG is here to help you uncover those stories.

## Project Goals

The primary goals of the LEG project are:

* **Centralize Resources:** To create a comprehensive collection of links, guides, and information relevant to genealogical research.
* **Foster Community:** To build a supportive and collaborative environment for genealogists of all skill levels to connect, share knowledge, and assist each other.
* **Promote Best Practices:** To advocate for accurate research methodologies, ethical data handling, and proper citation of sources in genealogical work.
* **Explore Technological Solutions:** To investigate and potentially develop open-source tools and utilities that can streamline and enhance the genealogical research process (this is a future aspiration).
* **Democratize Genealogy:** To make genealogical research more accessible to individuals regardless of their background or resources.

## Key Features

As a repository of resources and a community hub, LEG currently offers (or aims to offer):

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
* User Account Creation
* Different User Roles
* Comprehensive User Guide
* Timeline View: Displaying a chronological list of events for an individual or family
* Printable Charts and Reports (Basic): Generating simple PDF outputs of the current tree view or basic reports
* Interactive Tutorials and Tooltips: Guiding new users with on-screen help
* Advanced Search and Filtering (Basic): Allowing users to search by name, birth date, etc., and filter the tree view
* Customizable Node Appearance (Basic): Allowing users to change node colors or choose basic display options
* User Activity Logs (Admin-focused): Tracking basic user actions within family groups
* Guest Access (View-only): Allowing logged-in users to share a view-only link to their tree
* Discussion Forums within Groups (Basic): Implementing a simple forum structure within each family group
* Shared Media Library (Basic): Allowing file uploads and association with individuals (basic organization)
* Notification Bar for Admins

### Medium Complexity Features
* Source Citation and Management (Basic): Allowing users to add text-based citations to facts
* Visually Pleasing Family Tree (Basic): Creating a basic, interactive family tree using nodes and relationships
* Multiple Tree Styles: Implementing different visual layouts like fan charts or descendant charts
* Customizable Node Appearance (Advanced): Offering more granular control over node elements and information displayed
* Relationship Visualizations: Highlighting specific relationship paths within the tree
* Bulk Import/Export (GEDCOM Support): Parsing and generating GEDCOM files
* Advanced Search and Filtering (Complex): Allowing searches based on relationships, events, and other criteria
* Storytelling Features (Text-based): Allowing users to write and associate stories with individuals
* Event Planning & RSVP: Implementing a system for creating and managing family events with RSVP functionality
* Intuitive Drag-and-Drop Interface: For visually editing the family tree
* Geographic Mapping (Basic): Displaying event locations on a map using basic markers
* Option to create community/groups for the family tree

### Higher Complexity Features
* Detailed Node Information:
  * Name
  * Date of Birth
  * Profile Picture
  * Link to Social Profile
  * Achievements (listed date-wise)
* Customizable Layout of the Family Tree
* Fiction Character Example (Harry Potter)
* Record Hints/Suggestions: Integrating with external databases to suggest potential matches
* DNA Integration: Allowing users to link DNA results to their tree
* Research Collaboration Tools (Advanced): Implementing features like shared research logs and task assignment
* Privacy Controls (Granular): Implementing detailed privacy settings
* Shared Media Library (Advanced): Implementing features like tagging, searching within media, and version control
* Storytelling Features (Multimedia): Allowing the inclusion of photos, audio, and video in stories
* Printable Charts and Reports (Advanced): Generating highly customizable and visually rich reports
* Geographic Mapping (Advanced): Showing migration patterns and detailed historical maps
* Scalability and Performance Optimization (Ongoing)
* Accessibility (Comprehensive)
* Internationalization (i18n) and Localization (l10n)

## User Interface & Experience Design

The LEG application aims to provide a comprehensive platform for users to discover, document, and understand their family history through a clean, intuitive interface that makes complex information accessible.

### Overall Visual Style & Layout

#### Theme
* A blend of modern and classic aesthetics
* Clean, minimalist interface with an optional traditional theme (e.g., parchment backgrounds, serif fonts)
* Primary Colors: Earthy tones (browns, greens, creams) with modern accent colors (muted blue or deep red)

#### Layout Structure
* **Main Navigation:**
  * Persistent sidebar (collapsible on smaller screens) or top navigation bar
  * Key sections: Dashboard/Home, My Trees, Individuals, Sources, Media Library, Stories, Events, Community/Groups, Tools, Help/User Guide
* **Main Content Area:** Dynamic and interactive space powered by Livewire components
* **Footer:** Links to About, Privacy Policy, Code of Conduct, Support

### Key Screens and Visual Elements

#### 1. Dashboard/Home Screen
* Welcome message
* Summary statistics (number of individuals, recent activity)
* Recently viewed individuals or trees
* Quick action buttons (Add New Individual, Start New Tree, Import GEDCOM)
* Notifications and record hints section
* Community forum activity feed (if implemented)

#### 2. Family Tree View (D3.js Implementation)
* **Central Canvas:**
  * Interactive family tree visualization
  * Node representation for individuals
  * Default view: Clean boxes/circles with Name, Birth/Death Dates, Profile Picture
  * Detailed node view on hover/click
* **Customization Options:**
  * Control panel for node appearance
  * Multiple tree styles (Traditional Pedigree, Descendant, Fan, Radial)
* **Interactivity:**
  * Zoom and pan functionality
  * Click-to-expand node details
  * Drag-and-drop interface
  * Relationship path highlighting
* **Controls Toolbar:**
  * Zoom controls
  * Layout switcher
  * Search functionality
  * Add/Edit options
  * Print/Export features

#### 3. Individual Profile Page
* Header with key information
* Tabbed sections:
  * Overview
  * Timeline View
  * Facts/Events
  * Sources
  * Media Gallery
  * Stories
  * Relationships
  * Notes
* Action buttons for editing and adding content

#### 4. Source Citation Management
* Source list with filtering and search
* Detailed source entry forms
* Easy source linking interface
* Document upload and management

#### 5. Search and Filtering
* Advanced search interface
* Multiple filter options
* Results display with quick access to profiles

#### 6. Media Library
* Grid/List view of media items
* Advanced filtering and sorting
* Upload interface
* Detailed media view with metadata

#### 7. Storytelling Features
* Rich text editor
* Multimedia integration
* Clean story display interface

#### 8. Event Planning
* Event creation forms
* Guest list management
* Calendar view
* RSVP tracking

#### 9. Geographic Mapping
* Interactive map interface
* Location markers
* Migration pattern visualization
* Historical map overlays

#### 10. User Account Management
* Login/Registration pages
* User profile management
* Settings and preferences
* Admin dashboard

#### 11. Community/Groups
* Group directory
* Group home pages
* Discussion forums
* Shared resources section

#### 12. Interactive Tutorials
* Onboarding tour
* Contextual tooltips
* Help documentation

### Technology Integration

* **Livewire:**
  * Dynamic form updates
  * Real-time data synchronization
  * Responsive UI components
* **D3.js:**
  * Family tree visualizations
  * Timeline displays
  * Relationship diagrams
  * Geographic visualizations
* **Neo4j:**
  * Efficient relationship management
  * Complex query handling
  * Graph-based data structure

## Getting Started

This repository serves as the central point for the LEG project. Here's how you can get involved:

* **Explore the Resources:** Browse the various sections of this README and the linked resources to start your genealogical journey or enhance your existing research.
* **Contribute:** If you have valuable resources, links, or suggestions, please consider contributing to this repository. See the [Contributing](#contributing) section for more details.
* **Engage with the Community (Future):** Once the community forum is established, participate in discussions, ask questions, and share your knowledge.
* **Stay Updated:** Watch this repository for updates on new resources, community initiatives, and potential tool development.

### Contributing

We welcome contributions from the community! If you have valuable resources, links, educational materials, or ideas that align with the project goals, please follow these steps:

1.  **Fork the repository.**
2.  **Create a new branch** for your contribution (`git checkout -b feature/your-contribution`).
3.  **Make your changes.** Ensure your contributions are well-organized and clearly documented.
4.  **Submit a pull request.** Provide a clear and concise description of your changes and their purpose.

We appreciate your efforts in making LEG a valuable resource for the genealogical community.

### Code of Conduct

We are committed to fostering a welcoming and inclusive environment for all contributors and users. By participating in the LEG project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md) (link to be created). Please ensure you read and understand these guidelines.

## Resources

This section provides a categorized list of resources to aid your lineage exploration and genealogy research.

### Tools & Software

* **Online Family Tree Builders:**
    * [Ancestry.com](https://www.ancestry.com/) (Subscription-based)
    * [MyHeritage](https://www.myheritage.com/) (Subscription-based)
    * [FamilySearch](https://www.familysearch.org/) (Free)
    * [Geni.com](https://www.geni.com/) (Freemium)
* **Genealogy Software (Desktop):**
    * [Gramps](https://gramps-project.org/) (Free & Open Source)
    * [Legacy Family Tree](https://legacyfamilytree.com/) (Freemium)
    * [RootsMagic](https://rootsmagic.com/) (Paid)
* **Mobile Apps:**
    * (List popular genealogy apps for iOS and Android)
* **DNA Testing Services:**
    * [AncestryDNA](https://www.ancestry.com/dna/)
    * [23andMe](https://www.23andme.com/)
    * [MyHeritage DNA](https://www.myheritage.com/dna/)
* **Record Management Tools:**
    * (Suggest tools for organizing digital and physical records)

### Educational Materials

* **Online Courses & Tutorials:**
    * [National Genealogical Society (NGS)](https://www.ngsgenealogy.org/)
    * [Association of Professional Genealogists (APG)](https://www.apgen.org/)
    * [FamilySearch Learning Center](https://www.familysearch.org/learningcenter/)
    * [Coursera](https://www.coursera.org/) (Search for genealogy courses)
    * [edX](https://www.edx.org/) (Search for genealogy courses)
* **Genealogy Blogs & Websites:**
    * (List reputable genealogy blogs and informational websites)
* **Books & Guides:**
    * (Suggest foundational books on genealogical research)
* **Podcasts & YouTube Channels:**
    * (List relevant audio and video resources)

### Community Forum (Future)

* (Once established, provide a link and description of the forum)
    * **Ask Questions:** Get help with your research challenges.
    * **Share Discoveries:** Announce exciting finds and breakthroughs.
    * **Connect with Others:** Network with fellow genealogists based on location, surname, or research interests.
    * **Discuss Methodologies:** Share tips and best practices for effective research.

### Data Sources & Archives

* **Census Records:**
    * [National Archives and Records Administration (NARA)](https://www.archives.gov/) (US)
    * [The National Archives (UK)](https://www.nationalarchives.gov.uk/)
    * (List links to census records for other major countries)
* **Vital Records (Birth, Marriage, Death):**
    * (Provide general guidance and links to resources for finding vital records)
* **Immigration Records:**
    * [Ellis Island Archives](https://www.libertyellisfoundation.org/)
    * (List links to immigration records for various countries)
* **Military Records:**
    * (Provide links to military archives for different regions)
* **Probate Records:**
    * (Offer guidance on accessing probate records)
* **Land Records:**
    * (Provide general information on finding land records)
* **Historical Societies & Libraries:**
    * (Suggest resources for finding local historical societies and genealogical libraries)
* **Online Genealogical Databases:**
    * (List major subscription and free genealogical databases)

## Roadmap

The LEG project is continuously evolving. Here are some planned future developments:

* **Establish a Community Forum:** Creating a dedicated space for user interaction and support.
* **Develop Basic Genealogical Tools:** Exploring the feasibility of open-source tools for tasks like source citation management or simple family tree visualization.
* **Curate Regional Resources:** Expanding the list of data sources and archives with a focus on specific geographic regions.
* **Translate Resources:** Making key materials accessible in multiple languages.
* **Develop Learning Modules:** Creating structured educational content on specific genealogical topics.

## Support

If you have any questions, suggestions, or encounter any issues, please:

* **Open an issue** in this GitHub repository.
* (Once established) **Engage in the community forum.**

We appreciate your feedback and contributions!

## License

This project is licensed under the [MIT License](LICENSE). See the `LICENSE` file for more information.

## Acknowledgements

We would like to thank the following individuals and organizations for their contributions and support (this section will grow as the project evolves):

* (Acknowledge initial contributors and inspirations)
* The open-source community for providing valuable tools and resources.
* Genealogical societies and archives for their dedication to preserving history.

Thank you for being a part of the LEG project! Let's explore our roots together.