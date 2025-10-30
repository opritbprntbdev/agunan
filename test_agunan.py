"""
Unit tests for Agunan
"""
import unittest
from agunan import Agunan


class TestAgunan(unittest.TestCase):
    """Test cases for the Agunan class"""
    
    def setUp(self):
        """Set up test fixtures"""
        self.agunan = Agunan()
    
    def test_initialization(self):
        """Test that Agunan initializes with empty items"""
        self.assertEqual(len(self.agunan.get_items()), 0)
    
    def test_add_item(self):
        """Test adding an item"""
        result = self.agunan.add_item("House")
        self.assertTrue(result)
        self.assertEqual(len(self.agunan.get_items()), 1)
        self.assertIn("House", self.agunan.get_items())
    
    def test_add_multiple_items(self):
        """Test adding multiple items"""
        self.agunan.add_item("House")
        self.agunan.add_item("Car")
        self.agunan.add_item("Land")
        self.assertEqual(len(self.agunan.get_items()), 3)
    
    def test_remove_item(self):
        """Test removing an item"""
        self.agunan.add_item("House")
        result = self.agunan.remove_item("House")
        self.assertTrue(result)
        self.assertEqual(len(self.agunan.get_items()), 0)
    
    def test_remove_nonexistent_item(self):
        """Test removing an item that doesn't exist"""
        result = self.agunan.remove_item("Nonexistent")
        self.assertFalse(result)


if __name__ == "__main__":
    unittest.main()
