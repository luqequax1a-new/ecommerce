# Turkish Location Data Source

## Primary Source
- **URL**: https://mertmtn.github.io/CityDistrictJSONAPI/all-city-district.json
- **Original Source**: e-İçişleri Bakanlığı (Ministry of Interior)
- **Data Date**: 26.09.2023
- **Download Date**: 2025-08-28
- **File Size**: ~27KB

## Data Structure
- **Total Provinces**: 81 (complete Turkey coverage)
- **Total Districts**: ~973 (estimate based on source)
- **Format**: JSON with nested structure

## Data Quality
- Official government source via e-İçişleri
- Complete administrative hierarchy
- Includes plate codes for provinces
- District names normalized

## Alternative Sources
- **NPM Package**: Available for JS implementations
- **Separate Files**: City + District + Neighborhood JSON files
- **Extended Data**: Postal codes and neighborhood data available

## Usage in Project
- Used for Turkish address system (Turkey-only)
- Province → District dependency chain
- Normalized for database storage
- Cached for performance

## Updates
- Check source URL for data updates
- Re-download if administrative changes occur
- Update CHANGELOG.md when data is refreshed