# Finch
**Finch** is code sniffing tool, with specific rules set you can check your code style and recieve report output. **Finch** emerged from need of one company to keep code style clean and same on every project, we didn't wanted to make bigger differences across projects.

### Rules

The following rules are supported in this version, while we actively work on this project, many more will be supported in future. For example of some basic setup you can take a look at **rules** file.


* #### lineLength
    Value can be from 0 to n, it checks for maximum chars for each line.

* #### useBubbleSort
    Check for use statements sort from shorter to longer regarding string length.

* #### forceNamespace
    Forcing namespaces in files. No global scope is allowed.


* #### elseif
    Forbid usage of **else if** statements, if you use else if maybe its time for switch.

* #### else
    Forbids usage of else statement

* #### forbidInlineBracket
    Forbids usage of inline brackets, brackets for statements and declarations need to be in new line

* #### goto
    Forbid usage of **goto** statement, are you some kind of dinosaur? Be carefull for raptor

* #### eval
    Forbid usage of built in **eval** function.

* #### varByRef
    Passing variables by ref is forbiden

* #### returnByRef
    Returning references is forbiden

* #### globals
    Forbids usage of globals.

* #### indentSpace
    Force usage of spaces, using tabs is forbiden

* #### phpFileClosingTag
    Forbid usage of php file closing tag (?>) at the end of php files

* #### classStudlyCaps
    Class names must follow StudlyCaps naming rules

* #### methodsCamelCase
    Methods must follow camelCase naming rules

* #### vardump
    Detects usage of var_dump function
