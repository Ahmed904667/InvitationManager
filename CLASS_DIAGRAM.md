# Guest Manager Laravel - Complete System Class Diagram

This document contains a comprehensive class diagram for the Guest Manager Laravel application, showing ALL models, controllers, services, jobs, mail classes, and their relationships.

## Mermaid Class Diagram

```mermaid
classDiagram
    %% Core Models
    class User {
        +id: int
        +name: string
        +email: string
        +password: string
        +role: enum
        +is_active: boolean
        +last_login_at: datetime
        +scanner_settings: array
        +notification_settings: array
        +timezone: string
        +phone: string
        +profile_photo_path: string
        +bio: text
        +isAdmin(): boolean
        +isOrganizer(): boolean
        +isScanner(): boolean
        +hasRole(role): boolean
        +hasAnyRole(roles): boolean
    }

    class GuestList {
        +id: int
        +user_id: int
        +name: string
        +description: text
        +max_guests: int
        +settings: array
        +health: array
        +getDefaultSettings(): array
        +calculateAndStoreHealth(): array
    }

    class Guest {
        +id: int
        +guest_list_id: int
        +name: string
        +email: string
        +phone: string
        +timezone: string
        +group_id: int
        +language: string
        +notes: text
        +checked_in: boolean
        +checked_in_at: datetime
        +checked_in_by: int
        +check_in_notes: text
        +scanned_by_scanner_id: int
        +scanner_name: string
        +isCheckedIn(): boolean
    }

    class GuestGroup {
        +id: int
        +guest_list_id: int
        +name: string
        +description: text
        +color: string
        +getGuestCount(): int
        +getCheckedInCount(): int
    }

    class Event {
        +id: int
        +user_id: int
        +name: string
        +description: text
        +start_date: datetime
        +end_date: datetime
        +location: string
        +venue_name: string
        +venue_address: text
        +parking_info: text
        +additional_information: text
        +invitation_title: string
        +invitation_subtitle: string
        +invitation_message: text
        +rsvp_message: text
        +rsvp_deadline: datetime
        +rsvp_contact: string
        +rsvp_enabled: boolean
        +qr_checkin_enabled: boolean
        +qr_code_url: string
        +qr_description: text
        +invitation_platforms: array
        +message_mode: string
        +general_message: text
        +group_messages: array
        +per_guest_messages: array
        +ai_generated: boolean
        +hero_color1: string
        +hero_color2: string
        +accent_color: string
        +font_family: string
        +guest_list_ids: array
        +message_template: text
        +custom_messages: array
        +attachments: array
        +send_type: string
        +scheduled_at: datetime
        +status: string
        +dates_in_utc: boolean
        +cancelled_at: datetime
        +cancellation_reason: text
        +isCompleted(): boolean
        +isOngoing(): boolean
        +isUpcoming(): boolean
        +markAsRunning(): void
        +markAsCompleted(): void
        +canUseScanner(): boolean
        +createScanner(name): Scanner
        +getMainScannerUrl(): string
    }

    class EventGuest {
        +id: int
        +event_id: int
        +guest_id: int
        +status: string
        +removed_at: datetime
        +removal_reason: text
        +removed_by: int
        +checked_in: boolean
        +checked_in_at: datetime
        +checked_in_by: int
        +check_in_notes: text
        +scanned_by_scanner_id: int
        +scanner_name: string
        +isActive(): boolean
        +isRemoved(): boolean
        +isExpired(): boolean
        +markAsRemoved(reason, removedBy): void
        +markAsExpired(reason, removedBy): void
        +isCheckedIn(): boolean
        +checkIn(scanner, notes): void
        +undoCheckIn(): void
    }

    class Invitation {
        +id: int
        +event_id: int
        +guest_id: int
        +token: string
        +channel: string
        +recipient: string
        +message: text
        +status: string
        +sent_at: datetime
        +expired_at: datetime
        +rsvp_status: string
        +rsvp_at: datetime
        +rsvp_note: text
        +external_id: string
        +delivery_details: array
    }

    class Scanner {
        +id: int
        +event_id: int
        +name: string
        +timezone: string
        +token: string
        +is_active: boolean
        +last_used_at: datetime
        +settings: array
        +getScannerUrl(): string
        +updateLastUsed(): void
        +getCheckInCount(): int
        +getTimezone(): string
        +toScannerTimezone(datetime): Carbon
        +getCurrentTime(): Carbon
        +getRecentCheckIns(hours): Collection
    }

    class Trial {
        +id: int
        +contact: string
        +name: string
        +eventType: string
        +contact_method: string
        +invitation_message: text
        +status: string
        +ip_address: string
        +user_agent: string
        +invite_token: string
        +sample_event_data: array
        +rsvp_status: string
        +rsvp_note: text
        +rsvp_at: datetime
        +getInviteUrlAttribute(): string
        +isInviteValid(): boolean
    }

    %% Additional Models
    class AccountDeletionRequest {
        +id: int
        +user_id: int
        +token: string
        +email: string
        +requested_at: datetime
        +expires_at: datetime
        +confirmed: boolean
        +confirmed_at: datetime
        +isExpired(): boolean
        +isValid(): boolean
        +markAsConfirmed(): void
        +createForUser(user): AccountDeletionRequest
    }

    class Notification {
        +id: int
        +event_id: int
        +guest_id: int
        +user_id: int
        +type: string
        +channel: string
        +message: text
        +status: string
        +external_id: string
        +delivery_details: array
        +error_message: text
        +sent_at: datetime
        +delivered_at: datetime
        +failed_at: datetime
        +markAsQueued(): void
        +markAsDelivered(): void
        +markAsRead(): void
        +markAsFailed(): void
        +isSuccessful(): boolean
        +isFailed(): boolean
        +isInProgress(): boolean
    }

    class OTP {
        +id: int
        +user_id: int
        +type: string
        +identifier: string
        +code: string
        +expires_at: datetime
        +used: boolean
        +generateOTP(userId, type, identifier): string
        +validateOTP(userId, type, identifier, code): boolean
        +hasValidOTP(userId, type, identifier): boolean
        +cleanupExpired(): int
    }

    class Reminder {
        +id: int
        +invitation_id: int
        +event_id: int
        +guest_id: int
        +scheduled_for: datetime
        +platform: string
        +status: string
        +event_name: string
        +event_date: datetime
        +guest_name: string
        +guest_email: string
        +guest_phone: string
        +guest_timezone: string
        +message_content: text
        +subject: string
        +sent_at: datetime
        +error_message: text
        +attempts: int
        +last_attempt_at: datetime
        +job_id: string
        +queue: string
        +canBeSent(): boolean
        +isEmail(): boolean
        +isWhatsApp(): boolean
        +incrementAttempts(): void
        +markAsSent(): void
        +markAsFailed(errorMessage): void
    }

    class PasswordResetToken {
        +id: int
        +email: string
        +token: string
        +created_at: datetime
        +isExpired(): boolean
        +generateToken(email): string
        +validateToken(email, token): boolean
        +deleteToken(email): void
    }

    %% Controllers
    class AdminController {
        -adminService: AdminService
        +dashboard(): View
        +userManagement(): View
        +storeUser(request): RedirectResponse
        +showUser(user): View
        +updateUser(request, user): RedirectResponse
    }

    class OrganizerController {
        -organizerService: OrganizerService
    }

    class DashboardController {
        -organizerService: OrganizerService
        -eventReportService: EventReportService
        -eventReportPdfService: EventReportPdfService
    }

    class GuestListController {
        -organizerService: OrganizerService
    }

    class GuestController {
        -organizerService: OrganizerService
    }

    class ImportController {
        -organizerService: OrganizerService
    }

    class ScannerController {
        -scannerService: ScannerService
        +dashboard(): View
        +availableEvents(): View
        +scanEvent(guestList): View
        +scanGuest(request, guestList): JsonResponse
        +manualCheckIn(request, guestList): JsonResponse
        +searchGuest(request, guestList): JsonResponse
    }

    class AuthController {
        +showLogin(): View
        +login(request): RedirectResponse
        +googleLogin(request): RedirectResponse
        +googleRedirect(request): RedirectResponse
        +googleCallback(request): RedirectResponse
        +showRegister(): View
        +register(request): RedirectResponse
        +logout(request): RedirectResponse
        +showForgotPassword(): View
        +sendPasswordResetLink(request): RedirectResponse
        +showResetPassword(token): View
        +resetPassword(request): RedirectResponse
        +sendRegistrationOTP(request): JsonResponse
        +showVerifyOTP(): View
        +verifyRegistrationOTP(request): RedirectResponse
    }

    class AccountDeletionController {
        +requestDeletion(request): JsonResponse
        +confirmDeletion(token): View
        +showRequestForm(): View
    }

    class TrialController {
        +store(request): JsonResponse
        +showInvite(token): View
        +scheduleReminder(token, request): JsonResponse
        +deleteReminder(token, reminderId): JsonResponse
        +submitRsvp(token, request): RedirectResponse
    }

    class RsvpController {
        +show(token): View
        +submit(token, request): RedirectResponse
    }

    class TwilioWebhookController {
        +handleStatus(request): Response
        +updateNotificationStatus(notification, message): void
    }

    class LandingController {
        +index(): View
    }

    class HelpController {
        +index(): View
    }

    %% Services
    class AdminService {
        +getDashboardStats(): array
        +getAllUsers(): Collection
        +getUserStats(user): array
        +updateUser(user, data): User
        +createUser(data): User
    }

    class OrganizerService {
        +getAccountStatistics(): array
        +getDashboardStats(): array
        +getMyGuestLists(): Collection
        +updateGuestList(guestList, data): void
        +deleteGuestList(guestList): void
        +updateGuest(guest, data): void
        +getMyGuestListsWithFilters(): Collection
        +getGuestListDisplayData(guestList): array
        +getGuestListGuests(guestList): array
        +getGuestListStats(guestList): array
        +updateGuestListSettings(guestList, data): array
        +getGuestGroups(guestList): Collection
        +addGuestGroup(guestList, data): GuestGroup
        +updateGuestGroup(group, data): void
        +deleteGuestGroup(group): void
        +bulkChangeGuestGroup(guestList, data): array
        +createGuestList(data): GuestList
        +addGuest(guestList, data): Guest
        +bulkDeleteGuests(guestList, guestIds): array
        +deleteGuest(guest): void
        +getCountryCodes(): array
    }

    class ScannerService {
        +getDashboardStats(): array
        +getAvailableEvents(): Collection
        +getEventStats(guestList): array
        +scanGuest(guestList, identifier): array
        +manualCheckIn(guestList, guestId, notes): array
        +searchGuests(guestList, query): Collection
        +getGuestDetails(guest): array
        +getCheckInHistory(guestList): Collection
        +exportCheckIns(guestList): void
        +bulkCheckIn(guestList, guestIds): array
        +undoCheckIn(guest): array
        +getScannerSettings(): array
        +updateScannerSettings(settings): void
        +getOfflineData(): array
        +syncOfflineData(): array
    }

    class TwilioService {
        -client: Client
        -fromNumber: string
        +sendWhatsAppMessage(to, message): array
        +sendSMSMessage(to, message): boolean
        +formatPhoneNumber(phone): string
        +validatePhoneNumber(phone): boolean
        +getWebhookUrl(): string
        +updateNotificationStatusManually(messageSid, status): boolean
        +getMessageStatus(messageSid): object
    }

    class EventMessageGenerationService {
        -openaiApiKey: string
        -geminiApiKey: string
        +generateMessages(event, mode, guestData, userInstructions, preferredLanguage): array
        +editMessage(oldText, instructions): string
        +previewMessageForGuest(message, guest, event): string
        +validateParameters(event, mode, guestData): array
    }

    class ContactService {
        +validateContact(contact): array
        +determineContactMethod(contact): string
        +formatContact(contact, method): string
    }

    class EventGuestService {
        +addGuestToEvent(event, guest): EventGuest
        +removeGuestFromEvent(event, guest, reason, removedBy): void
        +checkInGuest(eventGuest, scanner, notes): void
        +undoCheckIn(eventGuest): void
    }

    class InvitationMessageService {
        +generateInvitationMessage(contact, name, eventType): array
        +validateContact(contact): array
        +determineContactMethod(contact): string
    }

    class PlatformStatsService {
        +getSystemStats(): array
        +getUserStats(user): array
        +getEventStats(event): array
    }

    class TrialEventGenerationService {
        +generateSampleEventData(eventType, name): array
        +createMockEvent(eventType, name): object
    }

    %% Jobs
    class SendEventReminder {
        +reminderId: int
        +timeout: int
        +tries: int
        +backoff: array
        +handle(): void
        +sendEmailReminder(reminder): void
        +sendWhatsAppReminder(reminder): void
        +updateNotificationStatusForLocalhost(reminder): void
        +failed(exception): void
    }

    class SendEventStartReminder {
        +eventId: int
        +handle(): void
    }

    class SendScheduledEventInvitations {
        +eventId: int
        +handle(): void
    }

    %% Mail Classes
    class OTPMail {
        +otpCode: string
        +userName: string
        +type: string
        +envelope(): Envelope
        +content(): Content
        +attachments(): array
    }

    class PasswordResetMail {
        +resetUrl: string
        +userName: string
        +expiresAt: string
        +envelope(): Envelope
        +content(): Content
    }

    class AccountDeletionConfirmationMail {
        +deletionRequest: AccountDeletionRequest
        +envelope(): Envelope
        +content(): Content
    }

    class EventCancellationMail {
        +event: Event
        +reason: string
        +envelope(): Envelope
        +content(): Content
    }

    class TrialRequestMail {
        +name: string
        +eventType: string
        +contact: string
        +invitationMessage: string
        +subject: string
        +inviteUrl: string
        +envelope(): Envelope
        +content(): Content
    }

    %% Policies
    class EventPolicy {
        +view(user, event): bool
        +create(user): bool
        +update(user, event): bool
        +delete(user, event): bool
    }

    class GuestListPolicy {
        +view(user, guestList): bool
        +create(user): bool
        +update(user, guestList): bool
        +delete(user, guestList): bool
    }

    %% Rules
    class StrongPassword {
        +passes(attribute, value): bool
        +message(): string
    }

    class UniqueInGuestList {
        +passes(attribute, value): bool
        +message(): string
    }

    %% Observers
    class EventObserver {
        +created(event): void
        +updated(event): void
        +deleted(event): void
    }

    %% Relationships - Core Models
    User ||--o{ GuestList : "creates"
    User ||--o{ Event : "organizes"
    User ||--o{ Guest : "checks in"
    User ||--o{ AccountDeletionRequest : "requests"
    User ||--o{ OTP : "has"
    User ||--o{ Notification : "receives"
    
    GuestList ||--o{ Guest : "contains"
    GuestList ||--o{ GuestGroup : "has"
    GuestList }o--o{ Event : "belongs to"
    
    Guest }o--|| GuestGroup : "belongs to"
    Guest ||--o{ Invitation : "receives"
    Guest ||--o{ EventGuest : "participates"
    Guest ||--o{ Notification : "receives"
    
    Event ||--o{ EventGuest : "has"
    Event ||--o{ Invitation : "sends"
    Event ||--o{ Scanner : "uses"
    Event ||--o{ Notification : "generates"
    Event ||--o{ Reminder : "schedules"
    
    EventGuest }o--|| Event : "belongs to"
    EventGuest }o--|| Guest : "belongs to"
    EventGuest }o--|| User : "checked in by"
    EventGuest }o--|| Scanner : "scanned by"
    
    Invitation }o--|| Event : "belongs to"
    Invitation }o--|| Guest : "belongs to"
    Invitation ||--o{ Reminder : "has"
    
    Scanner }o--|| Event : "belongs to"
    Scanner ||--o{ Guest : "scans"
    Scanner ||--o{ EventGuest : "scans"

    Reminder }o--|| Invitation : "belongs to"
    Reminder }o--|| Event : "belongs to"
    Reminder }o--|| Guest : "belongs to"

    Notification }o--|| Event : "belongs to"
    Notification }o--|| Guest : "belongs to"
    Notification }o--|| User : "belongs to"

    OTP }o--|| User : "belongs to"

    AccountDeletionRequest }o--|| User : "belongs to"

    %% Relationships - Controllers to Services
    AdminController --> AdminService : "uses"
    OrganizerController --> OrganizerService : "uses"
    DashboardController --> OrganizerService : "uses"
    GuestListController --> OrganizerService : "uses"
    GuestController --> OrganizerService : "uses"
    ImportController --> OrganizerService : "uses"
    ScannerController --> ScannerService : "uses"
    TrialController --> InvitationMessageService : "uses"
    TrialController --> TrialEventGenerationService : "uses"
    TrialController --> TwilioService : "uses"

    %% Relationships - Services to Models
    AdminService --> User : "manages"
    AdminService --> GuestList : "views"
    AdminService --> Guest : "views"
    
    OrganizerService --> User : "authenticates"
    OrganizerService --> GuestList : "manages"
    OrganizerService --> Guest : "manages"
    OrganizerService --> GuestGroup : "manages"
    OrganizerService --> Event : "views"
    
    ScannerService --> User : "authenticates"
    ScannerService --> GuestList : "scans"
    ScannerService --> Guest : "checks in"
    ScannerService --> Event : "views"

    TwilioService --> Notification : "updates"
    EventMessageGenerationService --> Event : "generates for"
    EventMessageGenerationService --> Guest : "personalizes for"
    EventGuestService --> EventGuest : "manages"
    EventGuestService --> Scanner : "uses"

    %% Relationships - Jobs to Models
    SendEventReminder --> Reminder : "processes"
    SendEventReminder --> Notification : "creates"
    SendEventReminder --> TwilioService : "uses"
    SendEventStartReminder --> Event : "processes"
    SendScheduledEventInvitations --> Event : "processes"

    %% Relationships - Mail to Models
    OTPMail --> OTP : "sends"
    PasswordResetMail --> PasswordResetToken : "sends"
    AccountDeletionConfirmationMail --> AccountDeletionRequest : "sends"
    EventCancellationMail --> Event : "sends"
    TrialRequestMail --> Trial : "sends"

    %% Relationships - Policies to Models
    EventPolicy --> Event : "authorizes"
    EventPolicy --> User : "authorizes"
    GuestListPolicy --> GuestList : "authorizes"
    GuestListPolicy --> User : "authorizes"

    %% Relationships - Observers to Models
    EventObserver --> Event : "observes"
```

## Key Relationships Explained

### Core Model Relationships

1. **User** is the central entity with three roles:
   - **Admin**: Full system access
   - **Organizer**: Event and guest list management
   - **Scanner**: Guest check-in functionality

2. **GuestList** belongs to a User and contains multiple Guests and GuestGroups

3. **Event** belongs to a User and can be associated with multiple GuestLists through the pivot table

4. **EventGuest** is the pivot model that connects Events and Guests, tracking participation status and check-ins

5. **Scanner** belongs to an Event and is used for guest check-ins

6. **Invitation** connects Events and Guests, tracking invitation status and RSVP responses

### Service Layer Architecture

The application follows a service-oriented architecture where:

- **Controllers** handle HTTP requests and delegate business logic to **Services**
- **Services** contain the business logic and interact with **Models**
- **Models** represent database entities and their relationships

### Role-Based Access Control

The system implements role-based access control through:

- User roles (admin, organizer, scanner)
- Role-specific controllers and services
- Authorization middleware
- Policy-based access control

## Usage

This class diagram can be used to:

1. **Understand the system architecture** - See how different components relate to each other
2. **Plan new features** - Identify where new functionality should be added
3. **Debug issues** - Trace relationships between entities
4. **Onboard new developers** - Provide a visual overview of the system
5. **Document the system** - Serve as living documentation

## Tools for Viewing

You can view this diagram using:
- GitHub (renders Mermaid diagrams automatically)
- Mermaid Live Editor (https://mermaid.live/)
- VS Code with Mermaid extension
- Any documentation tool that supports Mermaid
