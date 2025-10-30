#!/usr/bin/env python3
"""
Agunan - A simple application for managing collateral/guarantees
"""

class Agunan:
    """Main Agunan class for managing collateral items"""
    
    def __init__(self):
        self.items = []
    
    def add_item(self, item):
        """Add a collateral item"""
        if item is None or (isinstance(item, str) and not item.strip()):
            return False
        self.items.append(item)
        return True
    
    def get_items(self):
        """Get all collateral items"""
        return self.items.copy()
    
    def remove_item(self, item):
        """Remove a collateral item"""
        if item in self.items:
            self.items.remove(item)
            return True
        return False


def main():
    """Main entry point"""
    print("Agunan - Collateral Management System")
    agunan = Agunan()
    print(f"Initialized with {len(agunan.get_items())} items")


if __name__ == "__main__":
    main()
