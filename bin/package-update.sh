#!/bin/sh

# Output colorized strings
#
# Color codes:
# 0 - black
# 1 - red
# 2 - green
# 3 - yellow
# 4 - blue
# 5 - magenta
# 6 - cian
# 7 - white
output() {
	echo "$(tput setaf "$1")$2$(tput sgr0)"
}

# Autoloader
output 3 "Updating autoloader classmaps..."
composer dump-autoload
output 2 "Done"

output 3 "Patching libraries..."

sed -i '' -e 's/get_class()/__CLASS__/g' ./vendor/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/ApiResult.php
output 2 "Done!"