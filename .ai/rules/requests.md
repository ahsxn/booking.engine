---
paths:
  - 'app/Http/Requests/**'
---

# Requests

## Mutating endpoint requests
Customer-initiated mutating endpoints use dedicated Form Requests for validation and authorization. Signed third-party webhooks are exceptions: verify their signatures using the unmodified request body before processing. Business invariants must still be enforced inside actions and database transactions