fact := 1 ;
val := 10000 ;
cur := val ; mod := 1000000007 ;
while ( cur > 1 )
    do {
        fact := fact * cur ;
        fact := fact - fact / mod * mod ;
        cur := cur - 1;
    }
cur := 0;