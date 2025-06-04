# LEG - Lineage Exploration and Genealogy

<!-- 
[![Awesome](https://cdn.rawgit.com/sindresorhus/awesome/d7305f38d2aca4cf4c6f32d313e95770f38dd59d/media/badge.svg)](https://github.com/sindresorhus/awesome)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![GitHub Issues](https://img.shields.io/github/issues/pratikid/LEG.svg)](https://github.com/pratikid/LEG/issues)
[![GitHub Pull Requests](https://img.shields.io/github/pulls/pratikid/LEG.svg)](https://github.com/pratikid/LEG/pulls)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://opensource.org/)
 -->

**LEG** is a project dedicated to **Lineage Exploration and Genealogy**. It aims to provide resources, tools, and information for individuals interested in discovering, documenting, and understanding their family history. Whether you're a seasoned genealogist or just starting your ancestral journey, LEG offers a community and a collection of valuable materials to aid your exploration.

## Table of Contents

* [Introduction](#introduction)
* [Project Goals](#project-goals)
* [Documentation](#documentation)
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

## Documentation

- [Features](docs/FEATURES.md)
- [User Guide](docs/USER_GUIDE.md)
- [Setup Guide](docs/SETUP_GUIDE.md)
- [UI/UX Guide](docs/UI_UX.md)

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

## Local Development Setup

1. Clone the repository:
   ```sh
   git clone https://github.com/pratikid/LEG.git
   cd LEG
   ```
2. Copy the example environment file and configure your environment:
   ```sh
   cp .env.example .env
   # Edit .env as needed
   ```
3. Install PHP dependencies:
   ```sh
   composer install
   ```
4. Install Node dependencies:
   ```sh
   npm install
   ```
5. Build frontend assets:
   ```sh
   npm run dev
   ```
6. Run database migrations and seeders:
   ```sh
   php artisan migrate --seed
   ```
7. Start the local server:
   ```sh
   php artisan serve
   ```

## Running Tests

Run all tests using PHPUnit:
```sh
php artisan test
```

## Code Style

This project uses [Laravel Pint](https://laravel.com/docs/12.x/pint) for code style (PSR-12):
```sh
composer run lint
```

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