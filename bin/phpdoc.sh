#!/bin/sh

PHPDOC="bin/phpdoc.phar"
PHPDOCMD="vendor/evert/phpdoc-md/bin/phpdocmd"
SOURCE="app/"
HTML="docs/html/"
STRUCTURE="docs/"
STRUCTURE_FILE="docs/structure.xml"
DESTINATION="docs/md"

php $PHPDOC -d $SOURCE -t $HTML
php $PHPDOC -d $SOURCE -t $STRUCTURE --template="xml"
php $PHPDOCMD $STRUCTURE_FILE $DESTINATION