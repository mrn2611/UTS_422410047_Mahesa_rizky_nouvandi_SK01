# Diagram Bank Benih Pusaka

Diagram memakai sintaks Mermaid (tampil otomatis di GitHub, GitLab, VS Code dengan ekstensi Mermaid,
atau tempel ke https://mermaid.live untuk diekspor menjadi PNG/SVG).

## 1. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
  plant_groups ||--o{ accessions : "mengelompokkan"
  accessions ||--o| collection_origins : "memiliki asal"
  accessions ||--o{ accession_storage_treatment : "menerima"
  storage_treatments ||--o{ accession_storage_treatment : "diterapkan pada"

  plant_groups {
    bigint id PK
    varchar_100 name UK
    varchar_255 description "nullable"
    timestamp created_at
    timestamp updated_at
  }
  storage_treatments {
    bigint id PK
    varchar_100 name UK
    varchar_255 description "nullable"
    timestamp created_at
    timestamp updated_at
  }
  accessions {
    bigint id PK
    varchar_30 code UK
    varchar_150 name "index"
    bigint plant_group_id FK "restrict"
    timestamp created_at
    timestamp updated_at
  }
  collection_origins {
    bigint id PK
    bigint accession_id FK, UK "cascade"
    varchar_100 province "index"
    varchar_100 regency
    varchar_100 village "nullable"
    smallint altitude "nullable, mdpl"
    date collected_at
    varchar_100 collector "nullable"
    text notes "nullable"
    timestamp created_at
    timestamp updated_at
  }
  accession_storage_treatment {
    bigint id PK
    bigint accession_id FK "cascade"
    bigint storage_treatment_id FK "restrict"
    timestamp created_at
    timestamp updated_at
  }
```

Catatan: pasangan (`accession_id`, `storage_treatment_id`) pada tabel pivot memiliki unique constraint
`acc_treatment_unique`, sehingga satu perlakuan tidak bisa dipilih dua kali untuk aksesi yang sama.

## 2. Class diagram

```mermaid
classDiagram
  direction TB

  class AccessionController {
    <<Controller>>
    -AccessionService service
    +index(Request)
    +create()
    +store(StoreAccessionRequest)
    +show(Accession)
    +edit(Accession)
    +update(UpdateAccessionRequest, Accession)
    +destroy(Accession)
  }

  class DashboardController {
    <<Controller>>
    -AccessionStatisticsService stats
    +__invoke()
  }

  class AccessionStatisticsService {
    <<Service>>
    +summary() array
    +charts() array
    +latest(limit) Collection
    -perGroup() array
    -perProvince() array
    -perTreatment() array
    -perMonth() array
    -perAltitude() array
  }

  class StoreAccessionRequest {
    <<FormRequest>>
    +authorize() bool
    #prepareForValidation() void
    +rules() array
    +messages() array
    +attributes() array
  }

  class UpdateAccessionRequest {
    <<FormRequest>>
  }

  class AccessionService {
    <<Service>>
    +paginate(filters, perPage) Paginator
    +create(data) Accession
    +update(Accession, data) Accession
    +delete(Accession) void
    +nextCode() string
    -sortColumn(col) string
    -sortDirection(dir) string
  }

  class Accession {
    <<Model>>
    +int id
    +string code
    +string name
    +int plant_group_id
    +plantGroup() BelongsTo
    +origin() HasOne
    +treatments() BelongsToMany
    +scopeSearch(keyword) Builder
    +scopeFilter(filters) Builder
  }

  class PlantGroup {
    <<Model>>
    +int id
    +string name
    +string description
    +accessions() HasMany
  }

  class CollectionOrigin {
    <<Model>>
    +int id
    +int accession_id
    +string province
    +string regency
    +string village
    +int altitude
    +date collected_at
    +string collector
    +string notes
    +accession() BelongsTo
  }

  class StorageTreatment {
    <<Model>>
    +int id
    +string name
    +string description
    +accessions() BelongsToMany
  }

  StoreAccessionRequest <|-- UpdateAccessionRequest
  AccessionController --> AccessionService : memakai
  AccessionController ..> StoreAccessionRequest : validasi store
  AccessionController ..> UpdateAccessionRequest : validasi update
  AccessionService ..> Accession : transaksi CRUD
  DashboardController --> AccessionStatisticsService : memakai
  AccessionStatisticsService ..> Accession : agregasi
  AccessionStatisticsService ..> PlantGroup : agregasi
  AccessionStatisticsService ..> CollectionOrigin : agregasi
  AccessionStatisticsService ..> StorageTreatment : agregasi

  PlantGroup "1" --> "0..*" Accession : hasMany
  Accession "1" *-- "0..1" CollectionOrigin : hasOne
  Accession "0..*" -- "0..*" StorageTreatment : belongsToMany
```

## 3. Tabel kesesuaian diagram dengan implementasi

| Elemen diagram | Implementasi |
|---|---|
| `plant_groups` 1 — N `accessions` | `accessions.plant_group_id` FK `restrictOnDelete`; `Accession::plantGroup()` belongsTo, `PlantGroup::accessions()` hasMany |
| `accessions` 1 — 0..1 `collection_origins` | `collection_origins.accession_id` FK **unique** `cascadeOnDelete`; `Accession::origin()` hasOne, `CollectionOrigin::accession()` belongsTo |
| `accessions` N — M `storage_treatments` | tabel pivot `accession_storage_treatment` (FK `cascade` ke aksesi, `restrict` ke perlakuan, unique pasangan); `belongsToMany(...)->withTimestamps()` di kedua model |
| Aksesi wajib punya satu asal | database mengizinkan 0..1; aplikasi selalu membuat asal lewat `AccessionService::create()` dalam satu transaksi dan `origin` wajib pada Form Request |
| DashboardController → AccessionStatisticsService | controller invokable menerima service lewat constructor; service hanya melakukan agregasi (COUNT, GROUP BY) pada tabel yang sama dengan ERD, tanpa tabel baru |
| Controller → Service | `AccessionController` menerima `AccessionService` lewat constructor injection |
| Form Request | `UpdateAccessionRequest extends StoreAccessionRequest`; unik-kode memakai `Rule::unique()->ignore($this->route('accession'))` |
