name := $(shell basename `pwd`)
branch := $(shell hg branch)
date := $(shell date "+%s")

ifeq ($(wildcard Makefile.local),) 
deployplace := "testzilant@test$(name):./www/"
deployhost := "testzilant@test$(name)"
deployport := "22"
else 
include Makefile.local
endif

errors:
	tail /var/log/apache2/$(name).error.log -n 20

fix:
	gedit deltas/fix/$(date)fix.sql &

deltapp:
	gedit deltas/dev/${date}pp.sql &

deltacp:
	gedit deltas/dev/${date}cp.sql &

deltacc:
	gedit ../.deltas/dev/${date}cc.sql &

deltamc:
	gedit ../.deltas/dev/${date}mc.sql &

deltamp:
	gedit deltas/dev/${date}mp.sql &

deltamm:
	gedit ../.deltas/dev/${date}mm.sql &

py-deltapp:
	cp ../.zilant/migrate.py deltas/dev/${date}pp.py && \
	gedit deltas/dev/${date}pp.py &

py-deltacp:
	cp ../.zilant/migrate.py deltas/dev/${date}cp.py && \
	gedit deltas/dev/${date}cp.py &
	

py-deltacc:
	cp ../.zilant/migrate.py ../.deltas/dev/${date}cc.py && \
	gedit ../.deltas/dev/${date}cc.py &

py-deltamc:
	cp ../.zilant/migrate.py ../.deltas/dev/${date}mc.py && \
	gedit ../.deltas/dev/${date}mc.py &

py-deltamp:
	cp ../.zilant/migrate.py deltas/dev/${date}mp.py && \
	gedit deltas/dev/${date}mp.py &

py-deltamm:
	cp ../.zilant/migrate.py deltas/dev/${date}mm.py && \
	gedit deltas/dev/${date}mm.py &

localtest:
	./upload
	cd test && rm -f cookie.txt && phpunit Test.php

localtest-all:
	cd .. && phpunit AllTest.php
zip-src:
	echo Creating zip
	rm -f $(name).zip
	zip -9 $(name).zip . -r -x config/\* $(name).zip Makefile upload deltas/\* test/\* uploads/\* deploy/\*

zip-test:
	make zip-src
	echo Gathering deltas
	@if [ "$(shell ls -A ../.deltas/dev/*.sql 2>/dev/null)" ]; then\
	    cp ../.deltas/dev/*.sql ./deltas/; \
	fi
	@if [ "$(shell ls -A deltas/dev/*.sql 2>/dev/null)" ]; then\
	    cp deltas/dev/*.sql ./deltas/; \
	fi

	@if [ "$(shell ls -A deltas/pending/*.sql 2>/dev/null)" ]; then\
	    cp deltas/pending/*.sql ./deltas/; \
	fi

	zip -9 $(name).zip -m ./deltas/*.sql;\
	
	echo Done

zip-production: zip-src
	
	@if [ "$(shell ls -A deltas/pending/*.sql 2>/dev/null)" ]; then\
	    cp deltas/pending/*.sql ./deltas/; \
	fi

	zip -9 $(name).zip -m ./deltas/*.sql; 



zip-distr: zip-src
	echo adding config
	zip -9 ${name}.zip config -r

	@if [ "$(shell ls -A ../.deltas/done/*.sql 2>/dev/null)" ]; then\
	    cp ../.deltas/done/*.sql ./deltas/; \
	fi

	@if [ "$(shell ls -A deltas/done/*.sql 2>/dev/null)" ]; then\
	    cp deltas/done/*.sql ./deltas/; \
	fi

	@if [ "$(shell ls -A deltas/pending/*.sql 2>/dev/null)" ]; then\
	    cp deltas/pending/*.sql ./deltas/; \
	fi
	
	zip -9 $(name).zip -m ./deltas/*.sql; \


deploy-test: send-test
	ssh $(deployhost) -p $(deployport) ./deploy.sh	

send-test:
	scp -P $(deployport) $(name).zip $(deployplace)

send-production:
	@if [ '$(branch)' \= 'default' ]; \
	then \
	    scp $(name).zip zilant@prod$(name):./www/; \
	else \
	    echo "Not a stable branch. Test, merge, then test again. Good luck." ; \
	fi

publish-local:
	hg mv deltas/dev/*.sql deltas/pending/

publish-global:
	
	echo ../*/deltas/pending/ | xargs -n 1 hg cp ../.deltas/dev/*.sql
	hg mv ../.deltas/dev/*.sql ../.deltas/done/

commit-project-production:
	hg mv ./deltas/pending/*.sql ./deltas/done/

deploy-vm: zip-distr 
	cp ../.zilant/*.sh ./; \
	zip -9 $(name).zip -m ./*.sh; \
	echo "Distribution pack ready. Enter VM IP address"; \
	read VMADDR; \
	echo "Got address. Deploying test...";\
	scp $(name).zip testzilant@$$VMADDR:;\
	echo "Unpacking and setting up MySQL";\
	ssh testzilant@$$VMADDR "mv $(name).zip ./www && cd www && unzip $(name).zip && rm $(name).zip && sh ./mysqlsetup.sh test$(name) test$(name) test$(name) && rm ./mysqlsetup.sh && rm ./apachesetup.sh";\
	echo "Got address. Deploying production...";\
	scp $(name).zip zilant@$$VMADDR:;\
	echo "Unpacking and setting up MySQL";\
	ssh zilant@$$VMADDR "mv $(name).zip ./www && cd www && unzip $(name).zip && rm $(name).zip && sh ./mysqlsetup.sh $(name) $(name) $(name) && rm ./mysqlsetup.sh";\
	echo "setting up Apache web server";\
	ssh root@$$VMADDR "cd /home/zilant/www && sh ./apachesetup.sh $(name) && rm apachesetup.sh";\
	rm $(name).zip;\
