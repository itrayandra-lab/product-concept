## ADDED Requirements

### Requirement: Guest Form Auto-Save
The system SHALL automatically save guest user form data to localStorage and backend storage at regular intervals without user intervention.

#### Scenario: Auto-save form data on change
- **WHEN** a guest user enters or modifies data in any form field
- **THEN** the system SHALL save the data to localStorage after 2 seconds of inactivity (debounced)
- **AND** the system SHALL save the data to backend API every 30 seconds
- **AND** the system SHALL display a visual status indicator showing the current save state

#### Scenario: Handle localStorage save failure
- **WHEN** localStorage is full or unavailable
- **THEN** the system SHALL continue saving to backend API
- **AND** the system SHALL log a warning for debugging purposes

#### Scenario: Handle backend save failure
- **WHEN** the backend API save request fails
- **THEN** the system SHALL continue saving to localStorage
- **AND** the system SHALL display an error message to the user
- **AND** the system SHALL retry the save up to 3 times

### Requirement: Guest Session Restoration
The system SHALL restore guest form data after successful user authentication and automatically proceed with simulation generation.

#### Scenario: Restore form data after login
- **WHEN** a guest user fills out the form and clicks "Generate"
- **AND** the user successfully logs in with existing account
- **THEN** the system SHALL retrieve the saved form data from guest session
- **AND** the system SHALL automatically trigger simulation generation with the restored data
- **AND** the system SHALL delete the guest session after successful restoration

#### Scenario: Restore form data after registration
- **WHEN** a guest user fills out the form and clicks "Generate"
- **AND** the user successfully registers a new account
- **THEN** the system SHALL retrieve the saved form data from guest session
- **AND** the system SHALL automatically trigger simulation generation with the restored data
- **AND** the system SHALL display a success message: "Form data berhasil dipulihkan! Simulasi sedang diproses."

#### Scenario: Handle expired session during restoration
- **WHEN** a guest session has expired (older than 24 hours)
- **THEN** the system SHALL display an error message: "Session expired. Please fill the form again."
- **AND** the system SHALL redirect the user to the form page
- **AND** the system SHALL clear the expired session from localStorage

### Requirement: Form Progress Tracking
The system SHALL track form completion progress and display it to the user.

#### Scenario: Calculate form progress
- **WHEN** a guest user fills out form fields
- **THEN** the system SHALL calculate progress as a percentage (completed fields / total fields * 100)
- **AND** the system SHALL track which steps are completed (basic, target, ingredients, advanced)
- **AND** the system SHALL display the progress percentage in the UI

#### Scenario: Update progress on field change
- **WHEN** a user completes a form field
- **THEN** the system SHALL immediately update the progress calculation
- **AND** the system SHALL save the updated progress to session storage

### Requirement: Save Status Indicators
The system SHALL display visual indicators showing the current auto-save status to the user.

#### Scenario: Display saving status
- **WHEN** form data is being saved to backend
- **THEN** the system SHALL display "Saving..." with an appropriate icon
- **AND** the status SHALL be visible to the user in a non-intrusive manner

#### Scenario: Display saved status
- **WHEN** form data has been successfully saved
- **THEN** the system SHALL display "Saved" with timestamp
- **AND** the status SHALL auto-hide after 2 seconds

#### Scenario: Display error status
- **WHEN** form data save fails
- **THEN** the system SHALL display "Error saving data" message
- **AND** the status SHALL remain visible until the next save attempt

### Requirement: Before-Unload Warning
The system SHALL warn users about unsaved changes when attempting to leave the page.

#### Scenario: Warn about unsaved changes
- **WHEN** a user attempts to navigate away from the form page
- **AND** there are unsaved changes (data changed since last save)
- **THEN** the system SHALL show a browser confirmation dialog warning about unsaved changes
- **AND** the system SHALL allow the user to cancel navigation and save data

#### Scenario: Allow navigation with no unsaved changes
- **WHEN** a user attempts to navigate away from the form page
- **AND** there are no unsaved changes
- **THEN** the system SHALL allow navigation without warning

### Requirement: Form Data Validation
The system SHALL validate guest form data before saving and restoration.

#### Scenario: Validate required fields
- **WHEN** form data is submitted for validation
- **THEN** the system SHALL check that all 18 required fields are present
- **AND** the system SHALL check that field types match expected types (arrays, strings, numbers)
- **AND** the system SHALL return validation errors for invalid data

#### Scenario: Validate field types
- **WHEN** form data contains invalid field types (e.g., string instead of array)
- **THEN** the system SHALL reject the data
- **AND** the system SHALL return a descriptive error message

### Requirement: Session Cleanup
The system SHALL automatically clean up expired guest sessions.

#### Scenario: Cleanup expired sessions
- **WHEN** a guest session is older than 24 hours
- **THEN** the system SHALL delete the session from database
- **AND** the system SHALL clear the session from localStorage
- **AND** the system SHALL log the cleanup for monitoring purposes
