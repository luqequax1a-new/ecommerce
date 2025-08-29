# Turkish Location Data Changelog

All notable changes to the Turkish location data will be documented in this file.

## [1.0.0] - 2025-08-28

### Added
- Initial import of Turkish location data from e-İçişleri source
- Complete province and district data for Turkey
- 81 provinces with plate codes
- ~973 districts with proper normalization
- Database seeder implementation
- API endpoints for province/district lookup
- Caching system for performance optimization

### Data Source
- Source: https://mertmtn.github.io/CityDistrictJSONAPI/all-city-district.json
- Original Date: 26.09.2023
- Download Date: 2025-08-28

### Notes
- Data normalized to Title Case format
- Duplicate entries filtered during import
- Plate codes converted to integer IDs
- Support for Laravel cache system with 24-hour TTL