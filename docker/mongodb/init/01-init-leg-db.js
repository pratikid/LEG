// MongoDB initialization script for LEG application
// This script runs when the MongoDB container starts for the first time

// Switch to the leg database
db = db.getSiblingDB('leg');

// Create collections that will be used by the application
db.createCollection('individuals');
db.createCollection('families');
db.createCollection('notes');
db.createCollection('sources');
db.createCollection('media');

// Create indexes for better performance
db.individuals.createIndex({ "gedcom_xref": 1 });
db.individuals.createIndex({ "tree_id": 1 });
db.individuals.createIndex({ "individual_id": 1 });

db.families.createIndex({ "gedcom_xref": 1 });
db.families.createIndex({ "tree_id": 1 });
db.families.createIndex({ "family_id": 1 });

db.notes.createIndex({ "gedcom_xref": 1 });
db.notes.createIndex({ "tree_id": 1 });

db.sources.createIndex({ "gedcom_xref": 1 });
db.sources.createIndex({ "tree_id": 1 });

db.media.createIndex({ "gedcom_xref": 1 });
db.media.createIndex({ "tree_id": 1 });

// Create a user for the leg database with appropriate permissions
db.createUser({
    user: "leg_user",
    pwd: "password123",
    roles: [
        { role: "readWrite", db: "leg" },
        { role: "dbAdmin", db: "leg" }
    ]
});

print("LEG database initialized successfully"); 