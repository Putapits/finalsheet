# BPMN (Business Process Model and Notation) Guide for GSM Health System

## Table of Contents
1. [System Overview](#system-overview)
2. [BPMN Symbols & Elements](#bpmn-symbols--elements)
3. [BPMN Lanes (Swimlanes)](#bpmn-lanes-swimlanes)
4. [Main Processes](#main-processes)
5. [Detailed Process Flows](#detailed-process-flows)
6. [Cross-Lane Connections](#cross-lane-connections)
7. [Tool Recommendations](#tool-recommendations)

---

## System Overview

The **GSM (Government Services Management) Health System** is a multi-role healthcare and sanitation management platform with 5 user roles:

- **Admin**: System administrator, manages users, monitors notifications
- **Doctor**: Manages consultations, appointments, health surveillance
- **Nurse**: Assists with healthcare services, vaccinations, nutrition
- **Inspector**: Conducts sanitation inspections
- **Citizen**: Avails healthcare and sanitation services

### Key Services:
- **HCS**: Healthcare Consultation Services (appointments, consultations, emergency care)
- **HSS**: Health Surveillance System (disease & environmental monitoring)
- **INT**: Immunization & Nutrition Tracker
- **SPI**: Sanitation Permits & Inspection (business permits, health inspections)
- **WSS**: Wastewater & Septic Services (system inspection, maintenance, installation)

---

## BPMN Symbols & Elements

### 1. **Start Event**
```
Symbol: ⭕ (Circle)
Meaning: Beginning of a process
Color: Green (typically)
Notation: Small filled or unfilled circle
```

### 2. **End Event**
```
Symbol: ⭕ with thick border (Circle with double line)
Meaning: End of a process
Color: Red (typically)
Notation: Small circle with bold outline
```

### 3. **Task/Activity**
```
Symbol: ▭ (Rectangle with rounded corners)
Meaning: A single work activity
Color: Light blue/white (typically)
Notation: Rounded rectangle with descriptive text inside
Example: "Enter Patient Details", "Verify Appointment"
```

### 4. **Sub-Process**
```
Symbol: ▭ with + symbol (Rectangle with plus sign)
Meaning: A complex activity that breaks down into multiple steps
Color: Light blue/white
Notation: Rounded rectangle with + in center bottom
Example: "Create Appointment" (contains multiple steps)
```

### 5. **Decision/Gateway**
```
Symbol: ◇ (Diamond)
Meaning: A branching decision point (if-then logic)
Color: Light yellow/gold (typically)
Notation: Diamond shape
Paths: YES/NO or APPROVED/REJECTED
Example: "Is OTP Correct?", "Is User Verified?"
```

### 6. **Parallel Gateway**
```
Symbol: ◇ with + (Diamond with plus)
Meaning: Multiple activities happen simultaneously
Notation: Diamond with plus sign
```

### 7. **Inclusive Gateway**
```
Symbol: ◇ with ○ (Diamond with circle)
Meaning: One or more paths can be taken
Notation: Diamond with circle inside
```

### 8. **Data Object**
```
Symbol: 📄 (Document shape)
Meaning: Information or data involved in the process
Color: Light blue/gray
Notation: Paper/folder icon with label
Example: "OTP Email", "Appointment Details"
```

### 9. **Database/Data Store**
```
Symbol: 🗄️ (Cylinder/Database symbol)
Meaning: Persistent data storage
Notation: Cylinder shape or database icon
Example: "Users Database", "Appointments Table"
```

### 10. **Arrows/Sequence Flow**
```
Symbol: → (Arrow with solid line)
Meaning: Flow from one activity to another
Notation: Solid arrow line
Label: Optional condition or description
```

### 11. **Conditional Arrows**
```
Symbol: → with condition (Arrow with diamond at source)
Meaning: Flow that depends on a condition
Notation: Solid arrow with label describing condition
Example: "Yes" or "No" arrows from a Gateway
```

### 12. **Message Flow (Cross-Lane)**
```
Symbol: ⟶ (Dashed arrow)
Meaning: Communication between different lanes
Notation: Dashed or dotted arrow
Example: Doctor sends message to Nurse
```

### 13. **Lane (Swimlane)**
```
Symbol: | (Vertical divider)
Meaning: Represents a role, actor, or department
Notation: Vertical rectangle dividing the process
Width: Proportional to activity level
```

---

## BPMN Lanes (Swimlanes)

Lanes represent different actors/roles in your system. Each role has its own column.

### Lane Structure:
```
┌─────────────────────────────────────────────────────────┐
│ BPMN Diagram Title                                      │
├──────────────┬──────────────┬───────────────────────────┤
│    Admin     │    Doctor    │       Citizen             │
│              │              │                           │
│  [Activity1] │  [Activity2] │  [Activity3]             │
│      ↓       │      ↓       │      ↓                   │
│  [Decision] ─┼──────→ [Activity4]                      │
│              │              │                           │
└──────────────┴──────────────┴───────────────────────────┘
```

### For Your System - 5 Lanes:
1. **Citizen Lane** - Service requesters
2. **Healthcare Provider Lane** - Doctors & Nurses (can be combined or separate)
3. **Inspector Lane** - Sanitation inspectors
4. **Admin Lane** - System administration
5. **System Lane** - Automated processes (notifications, emails)

---

## Main Processes

### Process 1: User Authentication & Login
**Lanes**: Citizen, System

### Process 2: Appointment Booking & Consultation (HCS)
**Lanes**: Citizen, Doctor/Nurse, Admin, System

### Process 3: Health Surveillance (HSS)
**Lanes**: Doctor/Nurse, Citizen, System

### Process 4: Sanitation Inspection (SPI/WSS)
**Lanes**: Inspector, Citizen, Admin, System

### Process 5: User Management
**Lanes**: Admin, System

### Process 6: Immunization & Nutrition (INT)
**Lanes**: Nurse, Citizen, System

---

## Detailed Process Flows

### **PROCESS 1: USER AUTHENTICATION & LOGIN**

```
Lane: Citizen                    Lane: System
  ↓                                ↓
[Start] ──→ [Open Login Page]     [Wait for Input]
             ↓
        [Enter Email & Password]
             ↓
        [Submit Form] ────────────→ [Validate Credentials]
                                    ↓
                          ◇ Credentials Valid?
                         /           \
                       YES            NO
                        ↓              ↓
                  [Generate OTP] [Display Error]
                        ↓         [Redirect to Login]
                 [Send OTP Email] ←──┘
                        ↓
            [Check Email & Enter OTP]
                        ↓
            [Submit OTP] ──────────→ [Verify OTP]
                                     ↓
                          ◇ OTP Correct?
                         /            \
                       YES             NO
                        ↓              ↓
                  [Set Session]  [Display Error]
                        ↓         [Resend Option]
            [Redirect to Dashboard] ↙
                        ↓
                  [End - Success]
```

**Elements Used:**
- Start Event (⭕)
- End Event (⭕ with bold outline)
- Tasks: Open Login Page, Enter Email & Password, Submit Form, etc.
- Gateways (◇): Credentials Valid?, OTP Correct?
- Data Objects: Email, OTP, Session
- Message Flows: Validate Credentials (to System)

**Database Involved:**
- `users` table (credentials verification)
- `login_otps` table (OTP storage)

---

### **PROCESS 2: APPOINTMENT BOOKING & CONSULTATION (HCS)**

```
Lane: Citizen          Lane: Doctor/Nurse         Lane: Admin           Lane: System
  ↓                        ↓                         ↓                      ↓
[Start]
  ↓
[Browse Services]
  ↓
[Request Appointment] ──────────────────────────→ [Receive Request]
  ↓                                                   ↓
[Fill Details:                              [Store in Database]
 - Date, Time                                       ↓
 - Health Concerns              [Notification Alert] →→ [Email Admin]
 - Medical History]                                 ↓
  ↓                                        [Assign to Doctor/Nurse]
[Submit Request] ──────────────────────────→ ↓
                                        [Receive Notification] ───→ [Create Task]
                         ◇ Appointment Confirmed?
                        /                   \
                      YES                    NO
                       ↓                      ↓
                  [Schedule Set]        [Suggest Alternatives]
                       ↓                      ↓
              [Confirmation Email] ←─────────┘
                       ↓                      ↓
              [Add to Calendar]        [Update Status: Rejected]
                       ↓                      ↓
          [Citizen Receives Confirmation]
                       ↓
          [Attend Appointment]
                       ↓
          [Doctor: Complete Consultation]
                       ↓
          ◇ Prescription Needed?
         /              \
       YES              NO
        ↓                ↓
   [Issue Prescription] [Close Record]
        ↓                ↓
   [Send Report] ←──────┘
        ↓
   [Update Status: Completed]
        ↓
   [End - Success]
```

**Elements Used:**
- Multiple Tasks
- Parallel Gateway (appointments happen alongside notifications)
- Conditional Gateways (Appointment Confirmed?, Prescription Needed?)
- Data Objects: Appointment Details, Email, Prescription
- Cross-Lane Message Flows

**Database Involved:**
- `appointments` table
- `users` table
- `admin_notification_reads` table

---

### **PROCESS 3: HEALTH SURVEILLANCE (HSS)**

```
Lane: Doctor/Nurse              Lane: Citizen                Lane: System
  ↓                               ↓                            ↓
[Start]
  ↓
[Select HSS Service:
 - Disease Monitoring
 - Environmental Monitoring]
  ↓
[Create Monitoring Record] ─────────────→ [Citizen Receives Alert]
  ↓                                           ↓
[Monitor Patient/Environment]          [View Surveillance Data]
  ↓
[Collect Data/Samples]
  ↓
◇ Abnormality Detected?
/                \
YES               NO
 ↓                ↓
[Generate Alert]  [Continue Monitoring]
 ↓                ↓
[Create Report]   [Log Normal Status]
 ↓
[Send Notification to Citizen] ←───────→ [Citizen Acknowledges]
 ↓
[Update Status]
 ↓
[Archive Record]
 ↓
[End]
```

**Elements Used:**
- Tasks: Monitor Patient, Collect Data, Generate Alert
- Decision Gateway (Abnormality Detected?)
- Data Objects: Monitoring Reports, Alert Notifications
- Message Flows (Citizen notification)

**Database Involved:**
- `hss_disease_monitoring` table (disease records)
- `hss_environmental_monitoring` table (environmental data)
- Notifications system

---

### **PROCESS 4: SANITATION INSPECTION (SPI/WSS)**

```
Lane: Citizen               Lane: Inspector              Lane: Admin          Lane: System
  ↓                              ↓                          ↓                   ↓
[Start]
  ↓
[Request Service:
 - Business Permit
 - Health Inspection
 - Septic Service]
  ↓
[Submit Application] ──────────────────────────→ [Receive Request]
[Attach Documents]                                   ↓
  ↓                                            [Review Documents]
                                                    ↓
                                        ◇ Documents Complete?
                                       /              \
                                     YES              NO
                                      ↓               ↓
                                 [Schedule Visit]  [Request More Info]
                                      ↓               ↓
                                 [Notify Citizen] ←──┘
                                      ↓
[Inspector Visits Site]
  ↓
[Conduct Inspection] ──────────────→ [Document Findings]
  ↓
◇ Compliant?
/            \
YES           NO
 ↓            ↓
[Issue       [Issue Notice of
 Permit]     Non-Compliance]
 ↓            ↓
[Send Certificate] [Request Remediation]
 ↓            ↓
[Citizen    [Citizen Takes Action]
 Receives    ↓
 Approval]   [Resubmit for Inspection]
 ↓            ↓
[Archive    [Reinspection Process]
 Record]     ↓
             [Resolve Issues]
             ↓
             [Final Approval/Denial]
             ↓
[End - Success/Failure]
```

**Elements Used:**
- Task: Request Service, Submit Application, Schedule Visit
- Sub-Process: Conduct Inspection
- Decision Gateways: Documents Complete?, Compliant?
- Data Objects: Application, Inspection Report, Permit
- Cross-Lane Message Flows

**Database Involved:**
- `service_requests` table
- Inspection records
- Inspector assignments

---

### **PROCESS 5: USER MANAGEMENT**

```
Lane: Admin                    Lane: System
  ↓                               ↓
[Start]
  ↓
[Navigate to User Management]
  ↓
[Select Action:
 - Add User
 - Edit User
 - Block/Unblock User
 - View User Details]
  ↓
◇ Action Type?
├─→ [Add User]
│    ↓
│   [Enter User Details:
│    First/Last Name, Email,
│    Password, Role]
│    ↓
│   [Validate Email Uniqueness] →→ [Check Database]
│    ↓
│   ◇ Email Exists?
│  /              \
│ NO              YES
│  ↓              ↓
│ [Create User] [Show Error]
│  ↓
│ [Save to Database]
│  ↓
│ [Generate Temp Password Email]
│  ↓
│ [Send Email Notification] ←───→ [Email Service]
│  ↓
│ [Show Success Message]
│  ↓
├─→ [Edit User]
│    ↓
│   [Search User by Email/ID]
│    ↓
│   [Load User Details]
│    ↓
│   [Modify Fields]
│    ↓
│   [Validate Changes]
│    ↓
│   [Update Database]
│    ↓
│   [Log Action to Audit Trail]
│    ↓
│   [Show Success]
│    ↓
├─→ [Block/Unblock User]
│    ↓
│   [Select User]
│    ↓
│   [Confirm Action]
│    ↓
│   ◇ Confirm?
│  /              \
│ YES             NO
│  ↓              ↓
│ [Update Status] [Cancel]
│  ↓
│ [Log to Audit]
│  ↓
└─→ [End]
```

**Elements Used:**
- Exclusive Gateway (Action Type decision)
- Validation Tasks
- Data Storage (Database operations)
- Audit Logging

**Database Involved:**
- `users` table
- `audit_logs` table

---

### **PROCESS 6: IMMUNIZATION & NUTRITION (INT)**

```
Lane: Citizen                  Lane: Nurse                  Lane: System
  ↓                               ↓                           ↓
[Start]
  ↓
[Book Immunization/Nutrition Service]
  ↓
[Select Service Type:
 - Vaccination
 - Nutrition Monitoring]
  ↓
[Provide Details] ──────────────→ [Receive Request]
  ↓                                   ↓
                           [Schedule Appointment]
                                   ↓
                        [Send Confirmation] ←───→ [Email Notification]
                                   ↓
[Attend Appointment]
  ↓
[Provide Medical History] ─────→ [Record in System]
  ↓                                   ↓
[Undergo Service]              [Administer Service]
  ↓                                   ↓
[Receive Documentation] ←─ [Generate Certificate/Report]
  ↓
[View Schedule] ──────────────→ [Track Vaccination Schedule]
  ↓                                   ↓
                           ◇ Next Dose Due?
                          /            \
                        YES             NO
                         ↓              ↓
                   [Create Reminder] [Archive Record]
                         ↓              ↓
                  [Send Notification] ←─┘
                         ↓
[Citizen Receives Alert]
  ↓
[End]
```

**Elements Used:**
- Tasks: Book Service, Administer Service
- Gateways: Service Type selection, Dose Schedule decision
- Data Objects: Medical History, Vaccination Certificate
- Notifications

**Database Involved:**
- `immunizations` / `nutrition_records` tables
- `schedules` table

---

## Cross-Lane Connections

### **Type 1: Message Flow (Communication)**
Used when one actor sends information to another.

**Symbol**: Dashed Arrow (⟶)

**Examples:**
```
Citizen Lane    →---[Request]---→    Doctor Lane
Doctor Lane     →---[Appointment Confirmation]---→    Citizen Lane
Inspector Lane  →---[Visit Schedule]---→    Citizen Lane
System Lane     →---[Email Notification]---→    Citizen Lane
```

### **Type 2: Data Flow**
Used when data moves from one role to another through the system.

**Symbol**: Solid Arrow (→)

**Examples:**
```
[Citizen submits details] → [System processes] → [Doctor reviews]
[Inspector submits report] → [Admin uploads] → [System archives]
```

### **Type 3: Association**
Links data objects to activities.

**Symbol**: Dotted Arrow (· · ·)

**Examples:**
```
[Appointment Record] · · · · → [Doctor: Review Appointment]
[OTP Code] · · · · → [Citizen: Enter OTP]
```

---

## Implementation Guidelines

### **Step-by-Step: Creating a BPMN Diagram**

#### 1. **Define the Scope**
   - Identify the main process (e.g., "Appointment Booking")
   - List all actors/roles involved
   - Define start and end points

#### 2. **Identify Lanes**
   - Create one vertical lane per role
   - Arrange lanes logically (typically: Citizen → Provider → Admin → System)

#### 3. **Map Activities**
   - For each role, list all tasks they perform
   - Use rounded rectangles for regular tasks
   - Use rectangles with "+" for complex sub-processes

#### 4. **Define Decision Points**
   - Identify "if-then" scenarios
   - Use Diamond symbols (◇)
   - Label branches clearly (YES/NO, APPROVED/REJECTED, etc.)

#### 5. **Add Sequence Flow**
   - Draw arrows (→) connecting activities in order
   - Add conditions on conditional arrows
   - Ensure clear direction of flow

#### 6. **Add Cross-Lane Flows**
   - Use dashed arrows (⟶) for messages between lanes
   - Ensure sender and receiver are clear
   - Label the communication (what is being sent?)

#### 7. **Include Data Objects**
   - Add document/database symbols for important data
   - Link them to relevant activities with dotted arrows
   - Label clearly

#### 8. **Review and Validate**
   - Check that each activity is in the correct lane
   - Verify that all decision paths lead to an end event
   - Ensure no dangling activities
   - Confirm cross-lane communications are clear

---

## Tool Recommendations

### **Free Online BPMN Tools:**

1. **Lucidchart** (Free tier available)
   - URL: https://www.lucidchart.com
   - Features: Drag-and-drop, templates, collaboration
   - Best for: Professional diagrams

2. **Draw.io / Diagrams.net** (Free)
   - URL: https://app.diagrams.net
   - Features: Free, open-source, works offline
   - Best for: Quick diagrams, self-hosted

3. **Yodiz** (Free BPMN tools)
   - URL: https://yodiz.com
   - Features: BPMN specific, templates
   - Best for: BPMN compliance

4. **Camunda Modeler** (Free desktop app)
   - URL: https://camunda.com/products/camunda-cloud/modeler/
   - Features: Desktop application, BPMN 2.0 compliant
   - Best for: Technical implementation

5. **Bizagi Modeler** (Free)
   - URL: https://www.bizagi.com/en/products/bpm-suite/modeler
   - Features: Free tier, simulation capabilities
   - Best for: Process simulation

### **Recommended Approach for Your Project:**

1. **Start with Draw.io** (fastest way to get started)
   - Free, no registration needed
   - Good BPMN shapes library
   - Can export as PNG, PDF, SVG

2. **Move to Camunda Modeler** (if you need technical implementation)
   - Create executable BPMN diagrams
   - Can integrate with Camunda workflow engine
   - Export to standard BPMN 2.0 XML

---

## Example: Creating Process 1 (Authentication) in Draw.io

### Step-by-Step Instructions:

1. **Create Canvas and Lanes:**
   - Create two horizontal lanes (one smaller for "System")
   - Top lane: "Citizen", Bottom lane: "System"

2. **Add Start Event (Green Circle):**
   - Citizen Lane, left side

3. **Add Activities (Rounded Rectangles):**
   - "Open Login Page"
   - "Enter Email & Password"
   - "Submit Form"
   - "Check Email & Enter OTP"
   - "Submit OTP"

4. **Add Gateways (Diamonds):**
   - "Credentials Valid?"
   - "OTP Correct?"

5. **Add End Event (Red Circle):**
   - Right side, after successful login

6. **Add Message Flow (Dashed Arrows):**
   - From "Submit Form" (Citizen Lane) to "Validate Credentials" (System Lane)
   - From "Submit OTP" (Citizen Lane) to "Verify OTP" (System Lane)

7. **Add Conditional Arrows:**
   - From "Credentials Valid?" → YES to "Generate OTP"
   - From "Credentials Valid?" → NO to "Display Error"

8. **Add Data Objects:**
   - Email icon near "Generate OTP"
   - OTP icon near "Verify OTP"

---

## Key Process Variables by Module

### **HCS (Healthcare Consultation)**
- Service Types: medical-consultation, emergency-care, preventive-care
- Status: pending, confirmed, completed, cancelled
- Actors: Doctor, Nurse, Citizen, Admin

### **HSS (Health Surveillance)**
- Service Types: disease-monitoring, environmental-monitoring
- Status: pending, confirmed, completed, cancelled
- Actors: Doctor, Nurse, Citizen, Admin

### **INT (Immunization & Nutrition)**
- Service Types: vaccination, nutrition-monitoring
- Status: pending, confirmed, completed, cancelled
- Actors: Nurse, Citizen, Admin

### **SPI (Sanitation Permits)**
- Service Types: business-permit, health-inspection
- Status: pending, confirmed, completed, cancelled
- Actors: Inspector, Citizen, Admin

### **WSS (Wastewater & Septic)**
- Service Types: system-inspection, maintenance-service, installation-upgrade
- Status: pending, confirmed, completed, cancelled
- Actors: Inspector, Citizen, Admin

---

## Summary Table: BPMN Elements Quick Reference

| Symbol | Name | Use Case | Example |
|--------|------|----------|---------|
| ⭕ | Start Event | Begin process | Process starts |
| ⭕ ⭕ | End Event | Complete process | Login successful |
| ▭ | Task | Single activity | Enter email |
| ▭+ | Sub-Process | Complex activity | Conduct inspection |
| ◇ | Exclusive Gateway | One path only | Credentials valid? |
| ◇+ | Parallel Gateway | Multiple paths | Send notifications & update DB |
| 📄 | Data Object | Information used | OTP code |
| 🗄️ | Data Store | Database/storage | Users table |
| → | Sequence Flow | Activity order | Normal flow |
| ⟶ | Message Flow | Inter-lane communication | Send notification |
| \| | Lane | Role/Department | Doctor, Admin, System |

---

## Next Steps

1. **Choose a tool** from the recommendations above
2. **Create diagrams** for each main process
3. **Validate with stakeholders** (doctors, admins, inspectors)
4. **Document any exceptions** or alternate flows
5. **Keep diagrams updated** as business rules change
6. **Consider process mining** if you have transaction logs

---

## Additional Resources

- **BPMN 2.0 Standard**: https://www.bpmn.org/
- **OMG BPMN Specification**: https://www.omg.org/spec/BPMN/2.0/
- **Camunda BPMN Tutorial**: https://camunda.com/blog/2014/04/bpmn-tutorial/
- **BPMN Best Practices**: https://www.ek-solutions.com/blog/bpmn-best-practices/

---

**Document Version**: 1.0  
**Created**: January 14, 2026  
**System**: GSM Health & Sanitation Management System
