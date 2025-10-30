const { Collateral, CollateralManager } = require('./index.js');

// Simple test framework
let testsPassed = 0;
let testsFailed = 0;

function assert(condition, message) {
  if (condition) {
    console.log(`✓ ${message}`);
    testsPassed++;
  } else {
    console.error(`✗ ${message}`);
    testsFailed++;
  }
}

function assertThrows(fn, message) {
  try {
    fn();
    console.error(`✗ ${message}`);
    testsFailed++;
  } catch (e) {
    console.log(`✓ ${message}`);
    testsPassed++;
  }
}

console.log('\n=== Testing Collateral Class ===\n');

// Test Collateral creation
const collateral1 = new Collateral('C001', 'real-estate', 500000, 'House in Jakarta');
assert(collateral1.id === 'C001', 'Collateral ID is set correctly');
assert(collateral1.type === 'real-estate', 'Collateral type is set correctly');
assert(collateral1.value === 500000, 'Collateral value is set correctly');
assert(collateral1.status === 'active', 'Collateral status defaults to active');

// Test updateValue
collateral1.updateValue(550000);
assert(collateral1.value === 550000, 'Collateral value can be updated');

// Test updateStatus
collateral1.updateStatus('released');
assert(collateral1.status === 'released', 'Collateral status can be updated');

// Test invalid status
assertThrows(() => collateral1.updateStatus('invalid'), 'Invalid status throws error');

// Test toJSON
const json = collateral1.toJSON();
assert(json.id === 'C001', 'toJSON returns correct id');
assert(json.value === 550000, 'toJSON returns correct value');

console.log('\n=== Testing CollateralManager Class ===\n');

const manager = new CollateralManager();

// Test addCollateral
const c1 = manager.addCollateral('C001', 'real-estate', 500000, 'House in Jakarta');
assert(c1.id === 'C001', 'CollateralManager can add collateral');
assert(manager.getCount() === 1, 'CollateralManager count is correct');

const c2 = manager.addCollateral('C002', 'vehicle', 200000, 'Car');
const c3 = manager.addCollateral('C003', 'securities', 300000, 'Stocks');
assert(manager.getCount() === 3, 'Multiple collaterals can be added');

// Test duplicate ID
assertThrows(() => manager.addCollateral('C001', 'cash', 100000), 'Duplicate ID throws error');

// Test getCollateral
const retrieved = manager.getCollateral('C001');
assert(retrieved.id === 'C001', 'Can retrieve collateral by ID');

// Test getAllCollaterals
const all = manager.getAllCollaterals();
assert(all.length === 3, 'getAllCollaterals returns all collaterals');

// Test getCollateralsByType
const realEstate = manager.getCollateralsByType('real-estate');
assert(realEstate.length === 1, 'Can filter collaterals by type');

// Test updateCollateralValue
manager.updateCollateralValue('C002', 250000);
assert(manager.getCollateral('C002').value === 250000, 'Can update collateral value through manager');

// Test updateCollateralStatus
manager.updateCollateralStatus('C003', 'liquidated');
assert(manager.getCollateral('C003').status === 'liquidated', 'Can update collateral status through manager');

// Test getCollateralsByStatus
const active = manager.getCollateralsByStatus('active');
assert(active.length === 2, 'Can filter collaterals by status');

// Test getTotalActiveValue
const totalActive = manager.getTotalActiveValue();
assert(totalActive === 750000, 'Can calculate total active value (500000 + 250000)');

// Test getTotalValue
const total = manager.getTotalValue();
assert(total === 1050000, 'Can calculate total value of all collaterals');

// Test removeCollateral
manager.removeCollateral('C003');
assert(manager.getCount() === 2, 'Can remove collateral');
assert(manager.getCollateral('C003') === undefined, 'Removed collateral is not retrievable');

// Test remove non-existent collateral
assertThrows(() => manager.removeCollateral('C999'), 'Removing non-existent collateral throws error');

// Test update non-existent collateral
assertThrows(() => manager.updateCollateralValue('C999', 100), 'Updating non-existent collateral throws error');

console.log('\n=== Test Results ===\n');
console.log(`Tests passed: ${testsPassed}`);
console.log(`Tests failed: ${testsFailed}`);

if (testsFailed === 0) {
  console.log('\n✓ All tests passed!\n');
  process.exit(0);
} else {
  console.log(`\n✗ ${testsFailed} test(s) failed!\n`);
  process.exit(1);
}
