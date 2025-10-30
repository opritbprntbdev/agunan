const { CollateralManager } = require('./index.js');

console.log('=== Agunan - Collateral Management System Demo ===\n');

// Create a new collateral manager
const manager = new CollateralManager();

// Add some collateral assets
console.log('Adding collateral assets...');
manager.addCollateral('C001', 'real-estate', 500000, 'House in Jakarta');
manager.addCollateral('C002', 'vehicle', 200000, 'Toyota Camry 2020');
manager.addCollateral('C003', 'securities', 300000, 'Government bonds');
manager.addCollateral('C004', 'cash', 100000, 'Cash deposit');

console.log(`Total collaterals: ${manager.getCount()}\n`);

// Display all collaterals
console.log('All Collaterals:');
manager.getAllCollaterals().forEach(c => {
  console.log(`  - ${c.id}: ${c.type} - $${c.value.toLocaleString()} (${c.status})`);
});

// Calculate total values
console.log(`\nTotal value of all collaterals: $${manager.getTotalValue().toLocaleString()}`);
console.log(`Total value of active collaterals: $${manager.getTotalActiveValue().toLocaleString()}`);

// Update some collaterals
console.log('\nUpdating collateral values...');
manager.updateCollateralValue('C001', 550000);
console.log(`  - C001 value updated to $${manager.getCollateral('C001').value.toLocaleString()}`);

manager.updateCollateralStatus('C004', 'released');
console.log(`  - C004 status changed to: ${manager.getCollateral('C004').status}`);

// Filter by status
console.log('\nActive collaterals:');
manager.getCollateralsByStatus('active').forEach(c => {
  console.log(`  - ${c.id}: ${c.type} - $${c.value.toLocaleString()}`);
});

// Filter by type
console.log('\nReal estate collaterals:');
manager.getCollateralsByType('real-estate').forEach(c => {
  console.log(`  - ${c.id}: ${c.description} - $${c.value.toLocaleString()}`);
});

// Calculate new totals
console.log(`\nUpdated total value of active collaterals: $${manager.getTotalActiveValue().toLocaleString()}`);

console.log('\n=== Demo Complete ===');
