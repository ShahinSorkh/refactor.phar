##
# refactor.phar
#
# @file
# @version 0.1

all: clean test build

build:
	php -d 'phar.readonly=Off' src/bin/compile
	chmod +x refactor.phar

test: phpunit behat

phpunit:
	rm -rf reports/phpunit
	mkdir -p reports/phpunit
	php vendor/bin/phpunit

behat:
	rm -rf reports/behat
	mkdir -p reports/behat
	php vendor/bin/behat -f junit -f progress -o reports/behat -o /dev/stdout

clean:
	rm -f refactor.phar

# end
