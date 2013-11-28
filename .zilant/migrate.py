#!/usr/bin/python
# -*-coding:utf-8 -*-

import sys
import os
sys.path.append(os.path.abspath("../.zilant") )
from zdmigrate import *

"""    
t = TableChangeset("table name");
t.display_name = "Table SuperCaption"
t.create([String("ololo", "Just a string").invisible(), 
          Reference("reference", "Reference caption", "users")])

t.commit()
t = TableChangeset("anotherTable")
t.renameField("old_field", "new_field")
t.hideField("old_field")
t.dropField("field")
t.commit()
"""
