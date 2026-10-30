---
paths:
  - 'app/Data/**'
---

# Data

## Boundary DTOs
Use typed, immutable DTOs where they clarify HTTP-to-domain or action-result boundaries. Do not pass unchecked request arrays through domain operations, and do not create DTOs for trivial data without a useful boundary.
