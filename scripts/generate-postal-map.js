// Generate postal code map from cities API
const fs = require('fs');

async function generatePostalMap() {
  try {
    console.log('📡 Fetching all cities from API...');
    const citiesResponse = await fetch('https://api.twelvy.net/cities.php');
    const citiesData = await citiesResponse.json();

    const postalMap = {};
    const cities = citiesData.cities || [];

    console.log(`Found ${cities.length} cities, fetching postal codes...`);

    // Fetch postal code for each unique city
    for (const city of cities) {
      const cityUpper = city.toUpperCase();

      // Skip if already have this city (e.g., MARSEILLE without arrondissement)
      if (postalMap[cityUpper]) continue;

      try {
        const stagesResponse = await fetch(`https://api.twelvy.net/stages.php?city=${encodeURIComponent(cityUpper)}`);
        const stagesData = await stagesResponse.json();

        if (stagesData.stages && stagesData.stages.length > 0) {
          const postal = stagesData.stages[0].site.code_postal;
          postalMap[cityUpper] = postal;
          console.log(`  ✅ ${cityUpper} -> ${postal}`);
        }
      } catch (err) {
        console.warn(`  ⚠️ Failed to fetch ${cityUpper}:`, err.message);
      }
    }

    console.log(`\n✅ Generated map with ${Object.keys(postalMap).length} cities`);

    // Write to file
    const timestamp = new Date().toISOString();
    const content = `// Auto-generated postal code map
// Last updated: ${timestamp}
// Total cities: ${Object.keys(postalMap).length}

export const CITY_POSTAL_MAP: Record<string, string> = ${JSON.stringify(postalMap, null, 2)}
`;

    fs.writeFileSync('lib/city-postal-map.ts', content);
    console.log('✅ Written to lib/city-postal-map.ts');

  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

generatePostalMap();
