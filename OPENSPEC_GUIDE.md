# OpenSpec Guide - Panduan Lengkap

## Apa itu OpenSpec?

OpenSpec adalah **specification-driven development** tool yang membantu:
- **Mendokumentasikan** requirements dengan format yang konsisten
- **Melacak perubahan** melalui proposal → implementasi → archive
- **Memvalidasi** spesifikasi sebelum implementasi
- **Mengorganisir** development workflow

## Struktur OpenSpec di Project

```
openspec/
├── project.md              # Project context & conventions
├── AGENTS.md               # Instructions for AI assistants
├── specs/                  # Current truth - what IS built
│   ├── user-management/    # Authentication & user system
│   ├── content-management/ # Article & content system
│   ├── chatbot-integration/# AI chatbot system
│   └── web-platform/       # Public website
├── changes/                # Proposals - what SHOULD change
│   ├── implement-authentication-system/  # ✅ COMPLETED
│   └── archive/            # Completed changes
```

## Workflow OpenSpec (3 Stages)

### Stage 1: Creating Changes 📝

#### Kapan Membuat Proposal?
- Add features or functionality
- Make breaking changes (API, schema)
- Change architecture or patterns
- Optimize performance (changes behavior)
- Update security patterns

#### Kapan TIDAK Membuat Proposal?
- Bug fixes (restore intended behavior)
- Typos, formatting, comments
- Dependency updates (non-breaking)
- Configuration changes
- Tests for existing behavior

#### Langkah-langkah:

1. **Cek Context Existing**
```bash
openspec list                    # Active changes
openspec list --specs           # All capabilities
openspec show [spec-name] --type spec  # Show specific spec
```

2. **Buat Change Proposal**
```bash
# Pilih unique change-id (kebab-case, verb-led)
mkdir openspec/changes/add-article-comments/
mkdir openspec/changes/implement-content-management/
mkdir openspec/changes/update-user-dashboard/
```

3. **Scaffold Files**
```bash
# Buat 4 file utama:
# - proposal.md (Why, What, Impact)
# - tasks.md (Implementation checklist)
# - design.md (Technical decisions - optional)
# - specs/[capability]/spec.md (Delta spec)
```

4. **Tulis Delta Spec**
```markdown
## ADDED Requirements
### Requirement: New Feature
The system SHALL provide...

#### Scenario: Success case
- **WHEN** user performs action
- **THEN** expected result

## MODIFIED Requirements
### Requirement: Existing Feature
[Complete modified requirement]

## REMOVED Requirements
### Requirement: Old Feature
**Reason**: [Why removing]
**Migration**: [How to handle]
```

5. **Validate Proposal**
```bash
openspec validate [change-id] --strict
```

### Stage 2: Implementing Changes 🔨

1. **Baca Proposal & Tasks**
```bash
# Baca proposal.md untuk memahami why & what
# Baca design.md untuk technical decisions
# Baca tasks.md untuk implementation checklist
```

2. **Implement Step by Step**
```bash
# Implement tasks secara sequential
# Update task status setelah completion
# Test functionality
```

3. **Update Task Status**
```markdown
## 1. Database Setup
- [x] 1.1 Create articles table migration
- [x] 1.2 Create categories table migration
- [ ] 1.3 Create article_categories pivot table
- [ ] 1.4 Run migrations
```

### Stage 3: Archiving Changes 📦

1. **Archive Completed Change**
```bash
openspec archive [change-id] --yes
```

2. **Update Specs** (otomatis)
```bash
# OpenSpec akan update specs/ dengan perubahan
# Move change ke archive/
```

## Format Spec yang Benar

### Delta Format (untuk changes)
```markdown
## ADDED Requirements
### Requirement: New Feature
The system SHALL provide...

#### Scenario: Success case
- **WHEN** user performs action
- **THEN** expected result

## MODIFIED Requirements
### Requirement: Existing Feature
[Complete modified requirement dengan semua scenarios]

## REMOVED Requirements
### Requirement: Old Feature
**Reason**: [Why removing]
**Migration**: [How to handle]
```

### Scenario Format (CRITICAL)
```markdown
#### Scenario: User login success
- **WHEN** valid credentials provided
- **THEN** return JWT token
```

**❌ WRONG:**
```markdown
- **Scenario: User login**  ❌
**Scenario**: User login     ❌
### Scenario: User login      ❌
```

## Commands yang Sering Dipakai

### List & Show
```bash
openspec list                    # Active changes
openspec list --specs           # All capabilities
openspec show [item]            # Show details
openspec show [change] --json --deltas-only  # Debug deltas
```

### Validation
```bash
openspec validate [change] --strict    # Validate change
openspec validate --strict            # Validate all
```

### Archive
```bash
openspec archive [change] --yes      # Archive change
```

## Contoh Penggunaan di Project Lunaray

### Mau Tambah Fitur Baru?
```bash
# 1. Cek existing specs
openspec list --specs

# 2. Buat proposal
mkdir openspec/changes/add-article-comments/
# Buat proposal.md, tasks.md, specs/content-management/spec.md

# 3. Validate
openspec validate add-article-comments --strict

# 4. Implement
# 5. Archive
openspec archive add-article-comments --yes
```

### Mau Modify Existing Feature?
```bash
# 1. Cek current spec
openspec show content-management --type spec

# 2. Buat change proposal dengan MODIFIED requirements
# 3. Implement changes
# 4. Archive
```

## Best Practices

### ✅ DO:
- Gunakan `--strict` untuk validation
- Include `#### Scenario:` untuk setiap requirement
- Update task status setelah implementasi
- Archive setelah completion
- Gunakan kebab-case untuk change-id
- Verb-led prefixes: `add-`, `update-`, `remove-`, `refactor-`

### ❌ DON'T:
- Skip validation
- Forget scenario blocks
- Start implementation tanpa approval
- Leave changes unarchived
- Use bullet points untuk scenario headers

## Troubleshooting

### Common Errors

**"Change must have at least one delta"**
- Check `changes/[name]/specs/` exists with .md files
- Verify files have operation prefixes (## ADDED Requirements)

**"Requirement must have at least one scenario"**
- Check scenarios use `#### Scenario:` format (4 hashtags)
- Don't use bullet points or bold for scenario headers

**"Permission denied" saat archive**
- Manual move: `Move-Item changes/[name] changes/archive/YYYY-MM-DD-[name]`

### Validation Tips
```bash
# Always use strict mode
openspec validate [change] --strict

# Debug delta parsing
openspec show [change] --json --deltas-only

# Check specific requirement
openspec show [spec] --json -r 1
```

## File Templates

### proposal.md Template
```markdown
## Why
[1-2 sentences on problem/opportunity]

## What Changes
- [Bullet list of changes]
- [Mark breaking changes with **BREAKING**]

## Impact
- Affected specs: [list capabilities]
- Affected code: [key files/systems]
```

### tasks.md Template
```markdown
## 1. Implementation
- [ ] 1.1 Create database schema
- [ ] 1.2 Implement API endpoint
- [ ] 1.3 Add frontend component
- [ ] 1.4 Write tests
```

### design.md Template (Optional)
```markdown
## Context
[Background, constraints, stakeholders]

## Goals / Non-Goals
- Goals: [...]
- Non-Goals: [...]

## Decisions
- Decision: [What and why]
- Alternatives considered: [Options + rationale]

## Risks / Trade-offs
- [Risk] → Mitigation

## Migration Plan
[Steps, rollback]

## Open Questions
- [...]
```

## Status Project Saat Ini

```bash
# Current status
openspec list
# Output: implement-authentication-system ✓ Complete

# Available specs
openspec list --specs
# Output: user-management, content-management, chatbot-integration, web-platform
```

## Next Steps untuk Fase 3

### Options:
1. **Content Management** - `openspec show content-management --type spec`
2. **Chatbot Integration** - `openspec show chatbot-integration --type spec`
3. **Web Platform** - `openspec show web-platform --type spec`

### Workflow untuk Fase 3:
1. Pilih fitur yang mau dikerjakan
2. Buat change proposal
3. Implement step by step
4. Archive setelah completion

## Quick Reference

### Stage Indicators
- `changes/` - Proposed, not yet built
- `specs/` - Built and deployed
- `archive/` - Completed changes

### File Purposes
- `proposal.md` - Why and what
- `tasks.md` - Implementation steps
- `design.md` - Technical decisions
- `spec.md` - Requirements and behavior

### CLI Essentials
```bash
openspec list              # What's in progress?
openspec show [item]       # View details
openspec diff [change]   # What's changing?
openspec validate --strict # Is it correct?
openspec archive [change] [--yes|-y]  # Mark complete
```

## Remember
- **Specs are truth** - what IS built
- **Changes are proposals** - what SHOULD change
- **Keep them in sync**
- **Always validate before implementing**
- **Archive after completion**

---

*Dokumentasi ini dibuat untuk project Lunaray Beauty Factory dan dapat digunakan untuk project lain dengan OpenSpec.*
