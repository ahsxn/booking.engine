---
paths:
  - 'app/Jobs/**'
---

# Jobs

## Retry-safe asynchronous entry points
Jobs and scheduled commands orchestrate execution and delegate business transitions to actions. Make them tolerate retries; do not duplicate confirmation, release, or reconciliation rules in jobs.
