# Finch
**Finch** is code sniffing tool, with specific rules set you can check your code style and recieve report output. **Finch** emerged from need of one company to keep code style clean and same on every project, we didn't wanted to make bigger differences across projects.

For arguments that should be passed to **Finch** check **help.txt**

#### Project milestone:

- *Generate output in HTML*
- *Must and Should rules
     Must will be errors, should be warnings*

- *Custom rules*

#### Example output:

    ./tests/AnalyzeFiles/CamelCaseMethodsNotUsed.php  
    warning: 0  
    error: 2  
    method names must follow rules for camelCase naming: Line 7  
    method names must follow rules for camelCase naming: Line 12  
    -----------------------------------
    ./tests/AnalyzeFiles/Else_used.php  
    warning: 0  
    error: 2  
    else statement is forbiden: Line 13  
    else statement is forbiden: Line 22  
    -------------------------------------
    ./tests/AnalyzeFiles/Elseif_used.php  
    warning: 0  
    error: 1  
    else if statement is forbiden: Line 13  
    -----------------------------------
    ./tests/AnalyzeFiles/Eval_used.php  
    warning: 0  
    error: 1  
    have forbiden function eval(): Line 9  
    ---------------------------------
    ./tests/AnalyzeFiles/Globals.php  
    warning: 0  
    error: 1  
    using globals is forbiden: Line 9  
    -----------------------------------
    ./tests/AnalyzeFiles/Goto_used.php  
    warning: 0  
    error: 1  
    goto statement is forbiden: Line 9  
    -------------------------------------------
    ./tests/AnalyzeFiles/Namespace_Missing.php  
    warning: 0  
    error: 1  
    Namespace not found  
    -------------------------------------------
    ./tests/AnalyzeFiles/PhpFileClosingTag.php  
    warning: 0  
    error: 1  
    closing file tag ('?>') is forbiden: Line 10  
    -------------------------------------
    ./tests/AnalyzeFiles/ReturnByRef.php  
    warning: 0  
    error: 1  
    returning by reference is forbiden: Line 7  
    ----------------------------------------------------
    ./tests/AnalyzeFiles/StudlyCapsClassNameNotUsed.php  
    warning: 0  
    error: 1  
    class names must follow rules for StudlyCaps naming: Line 5  
    --------------------------------------------
    ./tests/AnalyzeFiles/UseClassBubbleSort.php  
    warning: 0  
    error: 1  
    use class bubble sorting not correct: Line 6  
    ----------------------------------
    ./tests/AnalyzeFiles/VarByRef.php  
    warning: 0  
    error: 1  
    passing by reference is forbiden: Line 7

### Rules

The following rules are supported in this version, while we actively work on this project, many more will be supported in future. For example of some basic setup you can take a look at **rules** file.


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
