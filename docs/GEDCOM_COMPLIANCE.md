# GEDCOM 5.5.5 Compliance Report for LEG

## Overview

LEG aims to provide full compatibility with the [GEDCOM 5.5.5 specification](https://www.gedcom.org/), the latest official standard for genealogical data exchange. GEDCOM 5.5.5 ensures interoperability with other genealogy applications and is widely supported in the industry.

---

## Compliance Checklist

| Area                | Status         | Notes/Action                                      |
|---------------------|---------------|---------------------------------------------------|
| Header/Encoding     | ✅ Compliant   | Use `2 VERS 5.5.5` in export header               |
| Individuals         | ✅ Compliant   | Add more optional tags as needed                  |
| Families            | ✅ Compliant   | Add export for `NOTE`/optional tags as needed     |
| Sources/Notes       | ⚠️ Partial    | Parsing OK, import/export/cross-linking: Future   |
| Cross-References    | ✅ Compliant   |                                                   |
| Relationships       | ✅ Compliant   |                                                   |
| Character Set       | ✅ Compliant   | Ensure all file ops use UTF-8                     |
| Trailer             | ✅ Compliant   |                                                   |
| Validation          | ➕ Recommended | Use [gedcom.org](https://www.gedcom.org/) tools   |

---

## Details

### Header and Encoding
- All required header lines are present in export.
- Version is set to `5.5.5`.
- Encoding is UTF-8 (`1 CHAR UTF-8`).

### Individual Records (`INDI`)
- Unique xrefs, required tags (`NAME`, `SEX`, `BIRT`, `DEAT`), and nested events are supported.
- Optional tags (ALIA, NICK, etc.) can be added as needed.

### Family Records (`FAM`)
- Unique xrefs, required tags (`HUSB`, `WIFE`, `CHIL`, `MARR`, etc.) and nested events are supported.
- Notes are parsed but not yet exported.

### Sources and Notes
- Parser supports sources and notes.
- Import/export and cross-linking are **planned for future implementation**.

### Cross-References and Relationships
- All xrefs are unique and correctly mapped.
- Relationships (spouse, parent-child) are created in both the database and Neo4j.

### Character Set
- All text is encoded as UTF-8.

### Trailer
- Export ends with `0 TRLR` as required.

### Validation
- Use [official GEDCOM validators and samples](https://www.gedcom.org/) to test import/export.

---

## Action Items for Full Compliance

1. Implement import/export and cross-linking for sources and notes.
2. Validate GEDCOM files with [official tools](https://www.gedcom.org/).
3. Update documentation to state: LEG supports GEDCOM 5.5.5, the latest official version as per [gedcom.org](https://www.gedcom.org/).

---

## References
- [GEDCOM Official Site & Specification](https://www.gedcom.org/)
- [GEDCOM 5.5.5 Specification PDF](https://www.gedcom.org/gedcom/gedcom-5-5-5.pdf)

---

*This document should be updated as LEG's GEDCOM support evolves.* 