git add -A
git commit  --allow-empty -am "zipped on `date` "
git archive --prefix=holaplex-wp/ -o release/release.zip HEAD
