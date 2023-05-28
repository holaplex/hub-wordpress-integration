git add -A
git commit -am "zipped on `date` "
git archive --prefix=holaplex-wp/ -o release/release.zip HEAD
