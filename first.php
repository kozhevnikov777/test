<?php 

$array = [
    [399, 9160, 144, 3230, 407, 8875, 1597, 9835],
    [2093, 3279, 21, 9038, 918, 9238, 2592, 7467],
    [3531, 1597, 3225, 153, 9970, 2937, 8, 807],
    [7010, 662, 6005, 4181, 3, 4606, 5, 3980],
    [6367, 2098, 89, 13, 337, 9196, 9950, 5424],
    [7204, 9393, 7149, 8, 89, 6765, 8579, 55],
    [1597, 4360, 8625, 34, 4409, 8034, 2584, 2],
    [920, 3172, 2400, 2326, 3413, 4756, 6453, 8],
    [4914, 21, 4923, 4012, 7960, 2254, 4448, 1]
];

function isFibonacci($number)
{
    if ($number == 0 || $number == 1) {
        return true;
    }
    
    $prev = 1;
    $curr = 1;
    
    while ($curr <= $number) {
        if ($curr == $number) {
            return true;
        }
        
        $next = $prev + $curr;
        $prev = $curr;
        $curr = $next;
    }
    
    return false;
}

function sumFibonacciNumbers($array)
{
    $sum = 0;
    
    foreach ($array as $row) {
        foreach ($row as $number) {
            if (isFibonacci($number)) {
                $sum += $number;
            }
        }
    }
    
    return $sum;
}


$result = sumFibonacciNumbers($array);

echo "Сумма чисел Фибоначчи в массиве: " . $result;

?>