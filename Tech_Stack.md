# Tech Stack – Finance Manager

**Status:** Finalized  
**Last Updated:** 2026-10-08  
**Temporary Project Name:** Finance Manager

---

## 1. Final Tech Stack

| Layer                    | Technology                              | Status     | Notes |
|--------------------------|-----------------------------------------|------------|-------|
| **Backend**              | Laravel (Latest)                        | Final      | You already know Laravel |
| **Authentication**       | Laravel Sanctum (Cookie-based SPA)      | Final      | More secure |
| **Frontend**             | React + Vite + **TypeScript**           | Final      | Learning TypeScript while building |
| **Styling**              | Tailwind CSS                            | Final      | - |
| **UI Components**        | Shadcn/UI                               | Final      | Clean & modern components |
| **State Management**     | **Zustand**                             | Final      | Simple global state (learning) |
| **Server State**         | TanStack Query (React Query)            | Final      | For API data, caching, loading |
| **Forms & Validation**   | React Hook Form + Zod                   | Final      | - |
| **Routing**              | React Router                            | Final      | - |
| **Charts**               | Recharts                                | Final      | For reports & dashboard |
| **Database**             | PostgreSQL                              | Final      | You want to learn it |
| **ORM**                  | Eloquent (Laravel)                      | Final      | - |
| **Email**                | Laravel Mail + Resend / Postmark / SES  | Final      | For Welcome & Budget emails |
| **File Storage**         | Local (MVP) → S3 later                  | Final      | For receipts |
| **Queue**                | Database Queue (MVP) → Redis later      | Final      | For sending emails |
| **Version Control**      | Git + GitHub                            | Final      | - |
| **Mobile (Future)**      | WebView (Android Studio) or Electron    | Future     | As planned |

---

## 2. Frontend Details

- **Language:** TypeScript (you will learn while building)
- **Build Tool:** Vite
- **Global State:** Zustand
- **API / Server State:** TanStack Query
- **UI Library:** Shadcn/UI + Tailwind CSS
- **Form Handling:** React Hook Form + Zod

---

## 3. Backend Details

- **Framework:** Laravel
- **Authentication:** Laravel Sanctum (SPA Cookie-based)
- **Database:** PostgreSQL
- **API Style:** RESTful API

---

## 4. Learning Path (For You)

While building this project, you will learn:

1. TypeScript (basic to intermediate)
2. Zustand
3. TanStack Query
4. Shadcn/UI
5. PostgreSQL
6. Better Laravel practices (Sanctum, Queues, Mail, etc.)

---

## 5. Future Additions

| Item                     | When                     |
|--------------------------|--------------------------|
| Redis                    | When needed for queues   |
| AWS S3 / Cloud Storage   | When file uploads grow   |
| Payment Gateway          | When adding Paid plans   |
| Push Notifications       | When building mobile app |
| Docker                   | Optional later           |

---

## Related Files

- Complete Planning: `Finance_Manager_Complete_Planning.md`
- Database Design: `Database_Design.md`
- Authentication: `Authentication.md`
- Features: `Features_Detailed.md`
