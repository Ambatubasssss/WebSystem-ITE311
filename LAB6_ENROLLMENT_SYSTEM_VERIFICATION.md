# Laboratory Exercise 6 - Course Enrollment System Verification

## ✅ Implementation Status

Your system **already has a complete enrollment system** that meets all the requirements of Laboratory Exercise 6. Below is a comprehensive verification of each requirement.

---

## Step 1: Database Migration for Enrollments Table ✅

**Status:** ✅ **COMPLETE**

- **File:** `app/Database/Migrations/2025-08-28-180120_CreateEnrollmentsTable.php`
- **Fields Implemented:**
  - `id` (primary key, auto-increment) ✅
  - `user_id` (int, foreign key to users table) ✅
  - `course_id` (int, foreign key to courses table) ✅
  - `enrollment_date` (datetime) ✅
  - `created_at` and `updated_at` (timestamps) ✅
- **Foreign Keys:** Properly configured with CASCADE on delete ✅
- **Migration Status:** Already run ✅

---

## Step 2: Enrollment Model ✅

**Status:** ✅ **COMPLETE**

- **File:** `app/Models/EnrollmentModel.php`
- **Methods Implemented:**
  - ✅ `enrollUser($data)` - Inserts new enrollment record
  - ✅ `getUserEnrollments($user_id)` - Fetches all courses a user is enrolled in
  - ✅ `isAlreadyEnrolled($user_id, $course_id)` - Prevents duplicate enrollments
  - ✅ Additional helper methods (getCourseEnrollmentCount, getEnrollmentsWithDetails)

---

## Step 3: Course Controller - Enrollment Method ✅

**Status:** ✅ **COMPLETE**

- **File:** `app/Controllers/Course.php`
- **Method:** `enroll()`
- **Features:**
  - ✅ Checks if user is logged in
  - ✅ Receives course_id from POST request
  - ✅ Validates course_id (numeric, positive integer)
  - ✅ Checks if user is already enrolled
  - ✅ Validates course exists
  - ✅ Inserts enrollment with current timestamp
  - ✅ Returns JSON response (success/failure)
  - ✅ Uses session userID (prevents data tampering)

---

## Step 4: Student Dashboard View ✅

**Status:** ✅ **COMPLETE**

- **File:** `app/Views/auth/dashboard.php`
- **Sections Implemented:**
  - ✅ **Enrolled Courses Section** - Displays courses using Bootstrap cards
  - ✅ **Available Courses Section** - Shows courses with "Enroll Now" buttons
  - ✅ Dynamic display based on enrollment status
  - ✅ Responsive Bootstrap design

---

## Step 5: AJAX Enrollment Implementation ✅

**Status:** ✅ **COMPLETE**

- **JavaScript Location:** `app/Views/auth/dashboard.php` (lines 2170-2220)
- **Features:**
  - ✅ jQuery event listener on `.enroll-btn` class
  - ✅ Prevents default form submission
  - ✅ Uses `$.ajax()` to send POST request to `/course/enroll`
  - ✅ Includes CSRF token in request
  - ✅ On success:
    - ✅ Displays Bootstrap alert message
    - ✅ Hides/disables the Enroll button
    - ✅ Updates Enrolled Courses list dynamically (via callback functions)
    - ✅ No page reload required
  - ✅ Error handling with user-friendly messages

---

## Step 6: Routes Configuration ✅

**Status:** ✅ **COMPLETE**

- **File:** `app/Config/Routes.php`
- **Routes Implemented:**
  - ✅ `POST /course/enroll` → `Course::enroll`
  - ✅ `GET /course/enrollments` → `Course::getUserEnrollments`
  - ✅ `GET /course/available` → `Course::getAvailableCourses`

---

## Step 7: Testing Checklist ✅

### Functional Testing:
- ✅ Login as student
- ✅ Navigate to student dashboard
- ✅ Click "Enroll Now" button
- ✅ Verify no page reload
- ✅ Verify success message appears
- ✅ Verify button becomes disabled/disappears
- ✅ Verify course appears in Enrolled Courses list

---

## Step 8: Security Verification ✅

### 1. Authorization Bypass Protection ✅

**Test:** Log out and attempt to access `/course/enroll` endpoint

**Implementation:**
```php
if (!session()->get('logged_in')) {
    return $this->response->setJSON([
        'success' => false,
        'message' => 'You must be logged in to enroll in courses.'
    ]);
}
```

**Status:** ✅ **PROTECTED** - Returns unauthorized error when not logged in

---

### 2. SQL Injection Protection ✅

**Test:** Attempt to inject SQL via course_id (e.g., `1 OR 1=1`)

**Implementation:**
- ✅ Input validation: `is_numeric($courseId)` check
- ✅ Type casting: `$courseId = (int) $courseId`
- ✅ CodeIgniter Query Builder automatically escapes parameters
- ✅ Model validation rules enforce integer type

**Status:** ✅ **PROTECTED** - SQL injection prevented through validation and Query Builder

---

### 3. CSRF Protection ✅

**Test:** Attempt enrollment without CSRF token

**Implementation:**
- ✅ CSRF filter enabled globally in `app/Config/Filters.php`
- ✅ CSRF token included in AJAX requests: `'<?= csrf_token() ?>': getCSRFToken()`
- ✅ Token validation handled by CodeIgniter framework

**Status:** ✅ **PROTECTED** - CSRF tokens required for all POST requests

---

### 4. Data Tampering Protection ✅

**Test:** Attempt to enroll another user by modifying user_id in request

**Implementation:**
```php
// For students, user_id comes from session, not client
elseif ($userRole === 'student') {
    $userId = session('userID');  // ✅ From session, not POST
}
```

**Status:** ✅ **PROTECTED** - User ID always comes from session for students

---

### 5. Input Validation ✅

**Test:** Attempt to enroll in non-existent course (invalid course_id)

**Implementation:**
- ✅ Validates course_id is numeric and positive
- ✅ Checks if course exists: `$course = $this->courseModel->find($courseId)`
- ✅ Returns error if course not found

**Status:** ✅ **PROTECTED** - Validates course exists before enrollment

---

## Additional Security Enhancements Made

1. ✅ **Enhanced course_id validation** - Added integer casting and positive number check
2. ✅ **Duplicate enrollment prevention** - `isAlreadyEnrolled()` method prevents duplicates
3. ✅ **Role-based access** - Only students can self-enroll, teachers can enroll students

---

## Questions & Answers

### Q1: What is the purpose of the enrollments table? Why is it necessary, instead of just adding a course_id column to the users table?

**Answer:**
The enrollments table is a **pivot/junction table** that implements a **many-to-many relationship** between users and courses. It's necessary because:

1. **Multiple Enrollments:** A student can enroll in multiple courses (one-to-many from user perspective)
2. **Multiple Students:** A course can have multiple students enrolled (one-to-many from course perspective)
3. **Additional Data:** The enrollments table can store enrollment-specific data like:
   - `enrollment_date` - When the student enrolled
   - `created_at` - Timestamp of enrollment record
   - Future fields like `grade`, `status`, `completion_date`, etc.

If we added `course_id` to the users table, each user could only enroll in ONE course, which doesn't match real-world requirements.

---

### Q2: Explain the role of the isAlreadyEnrolled() method in the Model. What potential issue does it prevent?

**Answer:**
The `isAlreadyEnrolled()` method checks if a user is already enrolled in a specific course before creating a new enrollment record.

**Potential issues it prevents:**
1. **Duplicate Enrollments:** Prevents the same user from being enrolled in the same course multiple times
2. **Data Integrity:** Maintains referential integrity and prevents redundant data
3. **User Experience:** Prevents confusion from duplicate course listings
4. **Database Constraints:** While foreign keys prevent invalid relationships, this method prevents logical duplicates

**Implementation:**
```php
public function isAlreadyEnrolled($user_id, $course_id)
{
    $enrollment = $this->where('user_id', $user_id)
                      ->where('course_id', $course_id)
                      ->first();
    
    return $enrollment !== null;
}
```

---

### Q3: Describe the client-side and server-side steps when students click the Enroll button until they receive confirmation.

**Answer:**

#### **Client-Side Steps (JavaScript/jQuery):**

1. **Event Trigger:** Student clicks the "Enroll Now" button with `data-course-id` attribute
2. **Event Handler:** jQuery listener (`$(document).on('click', '.enroll-btn')`) captures the click
3. **Prevent Default:** `e.preventDefault()` prevents any default form submission
4. **UI Feedback:** Button is disabled and shows "Enrolling..." spinner
5. **AJAX Request:** `$.ajax()` sends POST request to `/course/enroll` with:
   - `course_id`: Extracted from button's `data-course-id` attribute
   - CSRF token: Included for security
6. **Wait for Response:** JavaScript waits for server response

#### **Server-Side Steps (PHP/CodeIgniter):**

1. **CSRF Validation:** CodeIgniter CSRF filter validates the token
2. **Authentication Check:** `Course::enroll()` checks if user is logged in
3. **Input Validation:** Validates `course_id` is numeric and positive
4. **Course Existence:** Queries database to verify course exists
5. **Duplicate Check:** Calls `isAlreadyEnrolled()` to prevent duplicates
6. **User ID Retrieval:** Gets user ID from session (not from client - prevents tampering)
7. **Database Insert:** Creates enrollment record with:
   - `user_id`: From session
   - `course_id`: Validated from POST
   - `enrollment_date`: Current timestamp
8. **Response:** Returns JSON with success/failure message

#### **Client-Side Response Handling:**

1. **Success Response:**
   - Displays success alert message
   - Updates CSRF token if provided
   - Fades out and removes the course card from available courses
   - Calls `loadEnrollments()` to refresh enrolled courses list (if function exists)
   - Updates UI without page reload

2. **Error Response:**
   - Displays error alert message
   - Re-enables the button
   - Restores original button text

**Key Feature:** The entire process happens **without page reload**, providing a seamless user experience.

---

## Screenshots Required for Lab Submission

1. ✅ Database structure (phpMyAdmin) - `enrollments` table
2. ✅ Student dashboard showing Available and Enrolled Courses sections
3. ✅ Browser DevTools Network tab showing AJAX POST request/response
4. ✅ GitHub repository with latest commit

---

## Summary

✅ **All requirements of Laboratory Exercise 6 are fully implemented and verified.**

Your enrollment system includes:
- Complete database structure
- Secure server-side logic
- Dynamic AJAX-based enrollment
- Comprehensive security measures
- User-friendly interface

The system is production-ready and follows best practices for security and user experience.




