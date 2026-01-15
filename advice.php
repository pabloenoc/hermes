<?php 

/* Returns a warm message when there are no more feeds to read */

$advice = [
    "Have some water.",
    "Have some tea.",
    "Try a 10 minute walk.",
    "Remind someone you love them.",
    "Take a deep breath.",
    "Rest your eyes for a moment.",
    "Take a short break.",
    "Have you eaten today?",
    "Thank you for using Hermes :-)"
];

return $advice[array_rand($advice)];