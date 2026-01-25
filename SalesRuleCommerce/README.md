# SchrammelCodes SalesRule Commerce

Adobe Commerce add-on for the `SchrammelCodes_SalesRule` module, ensuring proper handling of Content Staging & Preview
when duplicating Cart Price Rules.

## What This Module Does

This is a companion module for `SchrammelCodes_SalesRule` specifically designed for Adobe Commerce Edition. 
It ensures that when you duplicate Cart Price Rules, the Commerce-specific Staging & Preview features work correctly.

### Why You Need This Module

Adobe Commerce Edition includes advanced Staging & Preview features that allow scheduling content and promotional
changes for future dates. These features use special database fields that need proper handling during duplication.

**Without this module:**
- Duplicated rules might inherit incorrect staging metadata
- Rules could appear in unexpected staging campaigns
- Database conflicts could occur

**With this module:**
- ✅ Duplicated rules are always created as new permanent rules
- ✅ Staging fields are properly reset to default values
- ✅ Duplicated rules are independent and ready for their own staging campaigns
- ✅ Store labels are preserved correctly

## How It Works

### Automatic Staging Field Management

When you duplicate a Cart Price Rule using the `SchrammelCodes_SalesRule` module, this Commerce extension automatically:

1. **Resets the Version Range**
   - Sets `created_in` to `1` (main permanent version)
   - Sets `updated_in` to `2147483647` (maximum version, meaning "forever")
   - This makes the duplicated rule a permanent rule, not tied to any staging campaign

2. **Clears the Row ID**
   - Removes the staging row ID so Magento generates a new one
   - Ensures the duplicated rule is truly independent in the database

3. **Preserves Store Labels**
   - Re-saves store-specific labels with the correct new row ID
   - Maintains your multi-store rule naming without manual re-entry

### What This Means for You

**Scenario**: You have a "Black Friday 2025" promotion scheduled as a staging campaign. You want to duplicate it as the
basis for "Cyber Monday 2025".

**Result**:
- The duplicated rule is created as a fresh, permanent rule
- It's not connected to the Black Friday staging campaign
- You can schedule it for Cyber Monday as a new staging campaign
- All store labels are preserved correctly

## Installation

### Prerequisites

This module **requires**:
1. **Adobe Commerce Edition** (not for Open Source)
2. **`SchrammelCodes_SalesRule`** module installed and enabled
3. **`Magento_SalesRuleStaging`** (included with Magento Commerce)

### Installation Steps

```bash
# Enable both modules
bin/magento module:enable SchrammelCodes_SalesRule SchrammelCodes_SalesRuleCommerce

# Run setup upgrade
bin/magento setup:upgrade

# Clear cache
bin/magento cache:clean
```

## Usage

This module works automatically in the background. Once installed, simply use the duplicate features from the
`SchrammelCodes_SalesRule` module as normal:

- Duplicate from grid actions
- Duplicate from rule edit page
- Mass duplicate multiple rules

The Commerce extension ensures all staging fields are handled correctly without any additional steps from you.

## Technical Details

### Architecture

- **Plugin-Based**: Uses an `afterDuplicate` plugin to extend the base duplication logic
- **Non-Intrusive**: Doesn't modify core Magento code or the base SalesRule module
- **Error-Safe**: Includes comprehensive error handling and logging

### Staging Field Values

The module sets these specific values for Commerce staging:

| Field | Value | Meaning |
|-------|-------|---------|
| `created_in` | `1` | Main timeline version |
| `updated_in` | `2147483647` | Maximum version (permanent) |
| `row_id` | `null` → auto-generated | New unique row identifier |

These values ensure the duplicated rule:
- Exists on the main timeline (not in a staging campaign)
- Has no end date in the staging system
- Gets a fresh database identifier

### Error Handling

If staging modifications fail (database errors, permission issues, etc.):
- The duplication still succeeds with the base rule data
- Errors are logged for administrator review
- Users see the duplicated rule (though staging fields might need manual verification)

## Compatibility

- **Adobe Commerce Edition 2.4.x**
- **PHP 8.1, 8.2, 8.3**
- **Requires `SchrammelCodes_SalesRule` 1.0.0+**

**Not compatible with Magento Open Source** (which doesn't include Staging features).

## Benefits for Commerce Users

### Workflow Efficiency
Duplicate rules from past staging campaigns and schedule them as new future campaigns without manual field adjustments.

### Data Integrity
Automatic handling of complex staging database relationships prevents orphaned records and database inconsistencies.

### Peace of Mind
Comprehensive unit testing and error handling ensure reliable operation even in edge cases.

### Multi-Store Support
Store-specific labels are automatically preserved and correctly linked to the new rule version.
