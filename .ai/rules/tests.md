---
paths:
  - 'tests/**'
---

# Tests

## Business and failure-path tests
Test business behavior and failure paths for each action. Capacity contention tests use real PostgreSQL with separate connections, not SQLite or transaction mocks; use deterministic injected failures and Stripe test mode where appropriate.
