rabbit:
	docker run --rm -it -p 15672:15672 -p 5672:5672 rabbitmq:3.9.13-management

composer:
	docker run --rm --interactive --tty \
	--network host \
	--env HOME \
    --env COMPOSER_HOME \
	--volume $(CURDIR):/app \
	--volume /etc/passwd:/etc/passwd:ro \
    --volume /etc/group:/etc/group:ro \
    --volume ${COMPOSER_HOME:-$HOME/.composer}:/tmp  \
    --user ${shell id -u}:$(shell id -g) \
	composer:2.2.5 \
	composer $(USER_CMD) --ignore-platform-reqs --prefer-dist

composer-install:
	make composer USER_CMD=install