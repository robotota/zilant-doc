# -*-coding:utf-8 -*-

import re

def quote(s):
    return '"' + s.replace('"', '\\"') +'"'

def pair(p):
    name, value = p
    return name +" = " + quote(value)

class Field():
    def __init__(self, name, caption, _type, realtype="text"):
        self.name = name
        self.caption = caption
        self.table = ""

        self.display = '1'
        self.display_in_grid = '1'
        self.editable = '1'
        self.type = _type
        self.foreign_table = ""
        self.foreign_name = ""
        self.options = ""
        self.realtype = realtype

    def hidden(self):
        self.display = '0'
        self.display_in_grid = '0'
        return self
    
    def readonly(self):
        self.editable = "0"
        return self

    def add_options(self, options_):
        self.options += options_ + " "
        return self

    def set_meta(self):
        mfields = {"table_name":self.table.name,
                   "name":self.name,
                   "display_name":self.caption,
                   "type":self.type,
                   "display":self.display,
                   "display_in_grid":self.display_in_grid,
                   "editable":self.editable,
                   "foreign_table_name":self.foreign_table,
                   "foreign_name" : self.foreign_name,
                   "options" :self.options}
        
        return "insert into __field_config (%s) values ( %s ) on duplicate key update %s;\n"%(", ".join(mfields.keys()), ", ".join(map(quote, mfields.values())), ", ".join(map(pair, mfields.items())))        
    def create(self):
        return self.name + " " + self.realtype

    def add(self):
        return "alter table "+self.table.name+" add column "+self.name + " "+self.realtype+";"

class ID (Field):
    def __init__(self):
        Field.__init__(self, 'id', "ID", 'id', "int(11)")
    def create(self):
        return Field.create(self)+" not null auto_increment primary key";

class String(Field):
    def __init__(self, name, caption):
        Field.__init__(self, name, caption, "string", "varchar(200)")


class PhoneNumbers(Field):
    def __init__(self, name, caption):
        Field.__init__(self, name, caption, "phonenumbers", "varchar(200)")

class Int(Field):
    def __init__(self, name, caption):
        Field.__init__(self, name, caption, "integer", "int(11)")

class Date(Field):
    def __init__(self, name, caption):
        Field.__init__(self, name, caption, "date", "date")

    def currentDate(self):
        return self.add_options("current_date")

class DateTime(Field):
    def __init__(self, name, caption):
        Field.__init__(self, name, caption, "datetime", "datetime")
    def currentDate(self):
        return self.add_options("current_date")

class Memo(Field):
    def __init__(self, name, caption):
        Field.__init__(self, name, caption, "memo", "longtext")

class Boolean(Field):
    def __init__(self, name, caption):
        Field.__init__(self, name, caption, "boolean", "bool")

class Reference(Field):
    def __init__(self, name, caption, foreign_table, foreign_name = ""):
        Field.__init__(self, name, caption, 'reference', "int(11)")
        self.foreign_table = foreign_table
        self.foreign_name = foreign_name

    def currentUser(self):
        return self.add_options("current_user_id")
    def nullable(self):
        return self.add_options("nullable")

class TableChangeset:
    def __init__(self, name):
        self.name = name
        self.display_name = name
        self.modifications = []
        self.mandatory_fields = [ID().readonly().hidden(), DateTime("date_created", "Создано").readonly().hidden(), Reference("created_by_id", "Кем создано", "users").readonly().hidden(), DateTime("date_modified", "Изменено").readonly().currentDate().hidden(), Reference("modified_by_id", "Кем изменено", "users").readonly().currentUser().hidden()]

    def create(self, fields):
        fields_to_create = self.mandatory_fields + fields 
        for f in fields_to_create:
            f.table = self
        mod = ""
        mod += "drop table if exists " + self.name + ";\n"
        mod += "create table " + self.name + "(\n"

        mod += ",\n".join(map( lambda x : "  "+x.create(), fields_to_create))
        mod += "\n);\n"        
        self.modifications.append(mod)

        self.modifications.append("insert into __display_names (table_name, display_name) values (\""+self.name+"\", \""+self.display_name+"\") " +
                                  "on duplicate key update table_name = \""+self.name+"\", display_name=\""+self.display_name+"\";")

        mod  = ""
        for f in fields_to_create:
            mod += f.set_meta();
        self.modifications.append(mod)
    
    def addField(self, field):
        field.table = self
        self.modifications.append(field.add())
        self.modifications.append(field.set_meta())

    def renameField(self, old_name, name):
        self.modifications.append("alter table %s rename %s to %s;" % (self.name, old_name, name))
        self.modifications.append("update __field_config  set name = \"%s\" where table_name = \"%s\" and name = \"%s\";"%(self.name, old_name, name))

    def dropField(self, name):
        self.modifications.append("alter table %s drop %s;" % (self.name, name))
        self.modifications.append("delete from __field_config where table_name = \"%s\" and name = \"%s\";"%(self.name, name))
        
    def hideField(self, name):
        self.modifications.append("update __field_config set display = 0, display_in_grid = 0 where table_name = \"%s\" and name = \"%s\" ;"%(self.name, name))
        
    def showField(self, name):
        self.modifications.append("update __field_config set display = '1', display_in_grid = '1' where table_name = \"%s\" and name = \"%s\" ;"%(self.name, name))
    

    def commit(self):
        for m in self.modifications:
            print m

