## 1. Backend API Implementation

- [x] 1.1 Create `GuestSessionController` with endpoints:
  - [x] POST /api/guest/save-form-data
  - [x] GET /api/guest/session/{session_id}
  - [x] DELETE /api/guest/session/{session_id}
  - [x] POST /api/simulations/generate-from-guest

- [x] 1.2 Implement form progress calculation logic
  - [x] Count completed form fields out of 18 total fields
  - [x] Calculate percentage completion (0-100%)
  - [x] Track which steps are completed (basic, target, ingredients, advanced)

- [x] 1.3 Update `GuestSessionRepository` to support progress tracking
  - [x] Add `form_step` and `completed_steps` fields handling
  - [x] Add `form_progress` calculation logic
  - [x] Add validation for required fields

- [x] 1.4 Add API routes to `routes/api.php`
  - [x] Guest endpoints (no authentication required)
  - [x] Generate from guest endpoint (requires authentication)

## 2. Frontend Auto-Save Implementation

- [x] 2.1 Create Alpine.js component for auto-save
  - [x] Initialize component with form data from localStorage
  - [x] Watch form data changes with `$watch()` and debounce (2 seconds)
  - [x] Implement `saveFormData()` method (localStorage + API)
  - [x] Handle save status states (saving, saved, error)
  - [x] Store last saved timestamp

- [x] 2.2 Implement localStorage integration
  - [x] Save form data to localStorage with session ID
  - [x] Load saved form data on component initialization
  - [x] Store `guest_session_id` in localStorage
  - [ ] Clear localStorage after successful authentication

- [x] 2.3 Add visual status indicators
  - [x] Display "Saving..." while API call is in progress
  - [x] Show "Saved" with timestamp after successful save
  - [x] Show "Error" message if save fails
  - [x] Auto-hide status after 2 seconds

- [x] 2.4 Add before-unload warning
  - [x] Check if there are unsaved changes
  - [x] Show browser confirmation dialog before page unload
  - [x] Save data if user chooses to stay

## 3. Guest Session Restoration

- [ ] 3.1 Implement restoration flow after login/registration
  - [ ] Check for `guest_session` parameter in URL
  - [ ] Fetch guest session data from API
  - [ ] Restore form data to form fields
  - [ ] Automatically trigger simulation generation
  - [ ] Delete guest session after successful restoration

- [ ] 3.2 Add restoration success messaging
  - [ ] Show notification: "Form data berhasil dipulihkan! Simulasi sedang diproses."
  - [ ] Redirect to simulation result page
  - [ ] Handle errors gracefully (expired session, invalid data)

## 4. Form Validation and Progress Tracking

- [x] 4.1 Implement form validation logic
  - [x] Validate required fields (all 18 fields)
  - [x] Validate field types (arrays, strings, numbers)
  - [ ] Show validation errors for invalid data

- [x] 4.2 Add progress tracking
  - [x] Calculate form progress based on completed fields
  - [x] Track which steps are completed (basic, target, ingredients, advanced)
  - [ ] Display progress indicator in UI (progress bar or percentage)

- [ ] 4.3 Add step navigation
  - [ ] Allow users to navigate between form steps
  - [ ] Save progress when moving to next step
  - [ ] Enable/disable navigation based on completion status

## 5. Error Handling and Edge Cases

- [x] 5.1 Handle session expiration
  - [x] Check if session is expired (24 hours)
  - [ ] Show notification: "Session expired. Please fill the form again."
  - [ ] Clear expired session from localStorage
  - [ ] Allow user to start fresh

- [x] 5.2 Handle network errors
  - [x] Retry failed API calls (max 3 retries)
  - [x] Show error message if all retries fail
  - [x] Fall back to localStorage-only mode
  - [x] Log errors for debugging

- [ ] 5.3 Handle large form data
  - [ ] Check localStorage size limits (5MB)
  - [ ] Compress form data if needed
  - [ ] Handle very large ingredient arrays

## 6. Testing

- [x] 6.1 Write feature tests for API endpoints
  - [x] Test POST /api/guest/save-form-data
  - [x] Test GET /api/guest/session/{session_id}
  - [x] Test DELETE /api/guest/session/{session_id}
  - [x] Test POST /api/simulations/generate-from-guest
  - [x] Test expiration handling

- [x] 6.2 Write unit tests for GuestSessionRepository
  - [x] Test form validation logic
  - [x] Test progress calculation
  - [x] Test step tracking
  - [x] Test session cleanup

- [ ] 6.3 Manual testing scenarios
  - [ ] Fill form as guest and verify auto-save
  - [ ] Test restoration after login
  - [ ] Test restoration after registration
  - [ ] Test session expiration
  - [ ] Test network failure handling
  - [ ] Test form validation errors
  - [ ] Test progress tracking accuracy

## 7. Documentation

- [ ] 7.1 Update API documentation
  - [ ] Document guest form endpoints
  - [ ] Add request/response examples
  - [ ] Document error codes and messages

- [ ] 7.2 Update user-facing documentation
  - [ ] Explain auto-save feature
  - [ ] Explain guest session behavior
  - [ ] Explain restoration flow

- [ ] 7.3 Update developer documentation
  - [ ] Document Alpine.js component usage
  - [ ] Document localStorage structure
  - [ ] Document API integration
