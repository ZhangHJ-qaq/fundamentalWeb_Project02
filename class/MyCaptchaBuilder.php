<?php


class MyCaptchaBuilder
{
    private $captchaQuestionText;
    private $captchaAnswer;

    public function generateCaptcha()
    {
        $operand1 = rand(1, 100);
        $operand2 = rand(1, 100);
        $operator = $this->getOperator();
        if ($operator === 'plus') {
            $this->captchaAnswer = $operand1 + $operand2;
            $this->captchaQuestionText = "验证码：$operand1 加 $operand2 结果是?";

        } else if ($operator === 'minus') {
            $this->captchaAnswer = $operand1 - $operand2;
            $this->captchaQuestionText = "验证码: $operand1 减 $operand2 结果是?";

        } else {
            $this->captchaAnswer = $operand1 * $operand2;
            $this->captchaQuestionText = "验证码: $operand1 乘 $operand2 结果是?";

        }

    }

    private function getOperator()
    {
        $num = rand(0, 2);
        $operator = '';
        switch ($num) {
            case 0:
                $operand = "plus";break;
                break;
            case 1:
                $operand = "minus";break;
                break;
            default:
                $operand = "times";break;
        }
        return $operator;

    }

    /**
     * @return mixed
     */
    public function getCaptchaQuestionText()
    {
        return $this->captchaQuestionText;
    }

    /**
     * @return mixed
     */
    public function getCaptchaAnswer()
    {
        return $this->captchaAnswer;
    }


}