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
	php -dxdebug.mode=coverage vendor/bin/phpunit --log-junit reports/phpunit/default.xml

behat:
	rm -rf reports/behat
	mkdir -p reports/behat
	php vendor/bin/behat -f junit -o reports/behat -f progress -o std

clean:
	rm -f refactor.phar

# end
